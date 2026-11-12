<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documentation extends CI_Controller
{
    private $api_controller_dir;
    private $api_root_controller_dir;
    private $api_routes_file;

    public function __construct()
    {
        parent::__construct();

        $this->api_controller_dir = FCPATH . 'api' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'api';
        $this->api_root_controller_dir = FCPATH . 'api' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'controllers';
        $this->api_routes_file = FCPATH . 'api' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.php';
    }

    public function index()
    {
        $this->load->view('swagger_ui');
    }

    public function json()
    {
        $json_file = FCPATH . 'api' . DIRECTORY_SEPARATOR . 'swagger.json';
        if (file_exists($json_file)) {
            $swagger_content = file_get_contents($json_file);
            $this->output
                ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
                ->set_header('Pragma: no-cache')
                ->set_content_type('application/json')
                ->set_output($swagger_content);
            return;
        }

        $swagger = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'School Management API',
                'description' => 'API documentation generated from API controllers.',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => rtrim(base_url(), '/')],
            ],
            'paths' => new stdClass(),
            'tags' => [],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-Key',
                    ],
                ],
            ],
        ];

        if (!is_dir($this->api_controller_dir)) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'error' => 'API controller directory was not found.',
                    'path' => $this->api_controller_dir,
                ]));
            return;
        }

        $paths = [];
        $route_map = $this->api_route_map();
        $controller_files = glob($this->api_controller_dir . DIRECTORY_SEPARATOR . '*.php');
        $auth_controller = $this->api_root_controller_dir . DIRECTORY_SEPARATOR . 'Auth.php';

        if (is_file($auth_controller)) {
            $controller_files[] = $auth_controller;
        }

        foreach ($controller_files as $file) {
            $class_name = basename($file, '.php');
            if (strtolower($class_name) === 'base') {
                continue;
            }

            $meta = $this->extract_swagger_meta($file);
            if (empty($meta['methods']) || !is_array($meta['methods'])) {
                continue;
            }

            $tag = !empty($meta['tag']) ? $meta['tag'] : $class_name;
            $swagger['tags'][] = [
                'name' => $tag,
                'description' => !empty($meta['description']) ? $meta['description'] : '',
            ];

            foreach ($meta['methods'] as $method => $method_meta) {
                $routes = $this->paths_for_method($route_map, $class_name, $method, $method_meta);
                foreach ($routes as $path) {
                    $http_method = !empty($method_meta['http_method']) ? strtolower($method_meta['http_method']) : 'get';

                    if (!isset($paths[$path])) {
                        $paths[$path] = [];
                    }

                    $paths[$path][$http_method] = $this->operation_spec($tag, $method, $method_meta, $path);
                }
            }
        }

        $swagger['paths'] = empty($paths) ? new stdClass() : $paths;

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function api_route_map()
    {
        $route_map = [];

        if (!is_file($this->api_routes_file)) {
            return $route_map;
        }

        $source = file_get_contents($this->api_routes_file);
        if ($source === false) {
            return $route_map;
        }

        preg_match_all("/\\\$route\\['([^']+)'\\]\\s*=\\s*'([^']+)'\\s*;/", $source, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $uri = $match[1];
            $target = preg_replace('#/\\$\\d+#', '', $match[2]);
            if (strpos($target, 'api/') !== 0) {
                continue;
            }

            if (!isset($route_map[$target])) {
                $route_map[$target] = [];
            }

            $route_map[$target][] = '/' . $this->openapi_path($uri);
        }

        return $route_map;
    }

    private function paths_for_method($route_map, $class_name, $method, $method_meta = [])
    {
        if (!empty($method_meta['path'])) {
            return ['/' . trim($method_meta['path'], '/')];
        }

        $controller = str_replace('_', '-', strtolower($class_name));
        $target = 'api/' . strtolower($class_name) . '/' . $method;

        if (!empty($route_map[$target])) {
            return $route_map[$target];
        }

        return ['/api/' . $controller . '/' . str_replace('_', '-', strtolower($method))];
    }

    private function operation_spec($tag, $method, $method_meta, $path)
    {
        $operation = [
            'summary' => !empty($method_meta['summary']) ? $method_meta['summary'] : ucfirst(str_replace('_', ' ', $method)),
            'description' => !empty($method_meta['description']) ? $method_meta['description'] : '',
            'tags' => [$tag],
            'responses' => !empty($method_meta['responses']) ? $method_meta['responses'] : [
                '200' => ['description' => 'Successful response'],
            ],
        ];

        foreach (['parameters', 'requestBody', 'security'] as $key) {
            if (!empty($method_meta[$key])) {
                $operation[$key] = $method_meta[$key];
            }
        }

        $path_parameters = $this->path_parameters($path);
        if (!empty($path_parameters)) {
            $operation['parameters'] = array_merge($path_parameters, isset($operation['parameters']) ? $operation['parameters'] : []);
        }

        return $operation;
    }

    private function openapi_path($uri)
    {
        $param_index = 1;
        $path = preg_replace_callback('#\\(:num\\)|\\(:any\\)#', function () use (&$param_index) {
            return '{param' . $param_index++ . '}';
        }, $uri);

        return trim($path, '/');
    }

    private function path_parameters($path)
    {
        if (!preg_match_all('/\\{([^}]+)\\}/', $path, $matches)) {
            return [];
        }

        $parameters = [];
        foreach ($matches[1] as $name) {
            $parameters[] = [
                'name' => $name,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'string'],
            ];
        }

        return $parameters;
    }

    private function extract_swagger_meta($file)
    {
        $source = file_get_contents($file);
        if ($source === false) {
            return [];
        }

        if (!preg_match('/public\\s+\\$swagger_meta\\s*=\\s*(\\[[\\s\\S]*?\\]);\\s*(?:public|private|protected)\\s+function/', $source, $matches)) {
            if (!preg_match('/public\\s+\\$swagger_meta\\s*=\\s*(\\[[\\s\\S]*?\\]);/m', $source, $matches)) {
                return [];
            }
        }

        try {
            $meta = eval('return ' . $matches[1] . ';');
        } catch (Throwable $e) {
            return [];
        } catch (Exception $e) {
            return [];
        }

        return is_array($meta) ? $meta : [];
    }
}
