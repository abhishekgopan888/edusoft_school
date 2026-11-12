<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Swagger extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('url');
    }

    public function index()
    {
        $this->load->view('swagger');
    }

    public function json()
    {
        $json_file = FCPATH . 'swagger.json';
        if (file_exists($json_file)) {
            $swagger_content = file_get_contents($json_file);
            $this->output
                ->set_content_type('application/json')
                ->set_output($swagger_content);
            return;
        }

        $swagger = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'School Management API',
                'description' => 'API documentation generated from controllers in application/controllers/api.',
                'version' => '1.0.0',
            ],
            'servers' => [
                ['url' => site_url()],
            ],
            'paths' => [],
            'security' => [
                ['BearerAuth' => []],
                ['ApiKeyAuth' => []]
            ],
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'Authorization',
                        'description' => 'Authorization header (e.g. Bearer <token>)',
                    ],
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-API-Key',
                        'description' => 'X-API-Key header',
                    ],
                ],
            ],
        ];

        $paths = [];
        $added_tags = [];

        // Scan api/ subdirectory controllers
        $api_controller_dir = APPPATH . 'controllers/api';
        foreach (glob($api_controller_dir . '/*.php') as $file) {
            $class_name = basename($file, '.php');
            if (strtolower($class_name) === 'base') {
                continue;
            }

            $meta = $this->extract_swagger_meta($file);
            if (empty($meta['methods']) || !is_array($meta['methods'])) {
                continue;
            }

            $tag = !empty($meta['tag']) ? $meta['tag'] : $class_name;
            if (!in_array($tag, $added_tags)) {
                $swagger['tags'][] = [
                    'name' => $tag,
                    'description' => !empty($meta['description']) ? $meta['description'] : '',
                ];
                $added_tags[] = $tag;
            }

            $base_path = '/' . str_replace('_', '-', strtolower($class_name));
            foreach ($meta['methods'] as $method => $method_meta) {
                $raw_path = !empty($method_meta['path']) ? $method_meta['path'] : $base_path . '/' . str_replace('_', '-', strtolower($method));
                // Strip leading /api/ prefix since server URL already includes the api base
                $path = preg_replace('#^/api/#', '/', $raw_path);
                $http_method = !empty($method_meta['http_method']) ? strtolower($method_meta['http_method']) : 'get';

                $method_spec = [
                    'summary' => !empty($method_meta['summary']) ? $method_meta['summary'] : ucfirst(str_replace('_', ' ', $method)),
                    'description' => !empty($method_meta['description']) ? $method_meta['description'] : '',
                    'tags' => [$tag],
                    'responses' => !empty($method_meta['responses']) ? $method_meta['responses'] : [
                        '200' => ['description' => 'Successful response'],
                    ],
                ];

                foreach (['parameters', 'requestBody', 'security'] as $key) {
                    if (array_key_exists($key, $method_meta)) {
                        $method_spec[$key] = $method_meta[$key];
                    }
                }

                if (!isset($paths[$path])) {
                    $paths[$path] = [];
                }
                $paths[$path][$http_method] = $method_spec;
            }
        }

        $swagger['paths'] = empty($paths) ? new \stdClass() : $paths;

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function extract_swagger_meta($file)
    {
        $source = file_get_contents($file);
        if ($source === false) {
            return [];
        }

        $start_pos = strpos($source, '$swagger_meta');
        if ($start_pos === false) {
            return [];
        }

        $eq_pos = strpos($source, '=', $start_pos);
        if ($eq_pos === false) {
            return [];
        }

        $bracket_pos = strpos($source, '[', $eq_pos);
        if ($bracket_pos === false) {
            return [];
        }

        $length = strlen($source);
        $depth = 0;
        $end_pos = false;
        $in_string = false;
        $string_char = '';

        for ($i = $bracket_pos; $i < $length; $i++) {
            $char = $source[$i];
            if ($in_string) {
                if ($char === $string_char && ($i === 0 || $source[$i - 1] !== '\\')) {
                    $in_string = false;
                }
            } else {
                if ($char === "'" || $char === '"') {
                    $in_string = true;
                    $string_char = $char;
                } elseif ($char === '[') {
                    $depth++;
                } elseif ($char === ']') {
                    $depth--;
                    if ($depth === 0) {
                        $end_pos = $i;
                        break;
                    }
                }
            }
        }

        if ($end_pos !== false) {
            $array_code = substr($source, $bracket_pos, $end_pos - $bracket_pos + 1);
            try {
                $meta = eval('return ' . $array_code . ';');
                if (is_array($meta)) {
                    return $meta;
                }
            } catch (\Throwable $e) {}
        }

        return [];
    }
}
