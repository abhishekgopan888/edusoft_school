<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Docs extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->load->view('admin/docs');
    }

    public function json()
    {
        $swagger = $this->build_openapi_spec();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function build_openapi_spec()
    {
        $swagger = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'School Management API',
                'description' => 'API documentation generated from controllers in api/application/controllers/api.',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => site_url('api'), 'description' => 'School API Server'],
            ],
            'paths' => new \stdClass(),
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

        $controller_dir = FCPATH . 'api/application/controllers/api';
        $paths = [];

        foreach (glob($controller_dir . '/*.php') as $file) {
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

            $base_path = '/api/' . str_replace('_', '-', strtolower($class_name));
            foreach ($meta['methods'] as $method => $method_meta) {
                $path = !empty($method_meta['path']) ? $method_meta['path'] : $base_path . '/' . str_replace('_', '-', strtolower($method));
                $http_method = !empty($method_meta['http_method']) ? strtolower($method_meta['http_method']) : 'get';

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

                if (!isset($paths[$path])) {
                    $paths[$path] = [];
                }

                $paths[$path][$http_method] = $operation;
            }
        }

        $swagger['paths'] = empty($paths) ? new \stdClass() : $paths;
        return $swagger;
    }

    private function extract_swagger_meta($file)
    {
        $source = file_get_contents($file);
        if ($source === false) {
            return [];
        }

        if (!preg_match('/public\s+\$swagger_meta\s*=\s*(\[[\s\S]*?\]);\s*(?:public|private|protected)\s+function/', $source, $matches)) {
            if (!preg_match('/public\s+\$swagger_meta\s*=\s*(\[[\s\S]*?\]);/m', $source, $matches)) {
                return [];
            }
        }

        try {
            $meta = eval('return ' . $matches[1] . ';');
        } catch (\Throwable $e) {
            return [];
        } catch (\Exception $e) {
            return [];
        }

        return is_array($meta) ? $meta : [];
    }
}
