<?php
/**
 * Swagger Generator Script for School Management API
 * Generates both swagger.json and swagger.yaml
 */

$swagger = [
    'openapi' => '3.0.0',
    'info' => [
        'title' => 'School Management API',
        'description' => 'Comprehensive API documentation for School Management System including Authentication, Drivers, Attenders, Students, Student Details, Dashboard, and System Health endpoints.',
        'version' => '1.0.0',
    ],
    'servers' => [
        ['url' => '/api', 'description' => 'Current Server (Auto /api - Recommended for all hosts)'],
        ['url' => 'https://school.onedusoft.in/api', 'description' => 'Production Server (school.onedusoft.in)'],
        ['url' => 'http://localhost:8000/api', 'description' => 'Local Development Server'],
        ['url' => 'http://localhost/school/api', 'description' => 'Local Wamp Server'],
    ],
    'tags' => [],
    'paths' => [],
    'security' => [
        ['BearerAuth' => []],
        ['ApiKeyAuth' => []]
    ],
    'components' => [
        'securitySchemes' => [
            'BearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
                'description' => 'Bearer token authentication',
            ],
            'ApiKeyAuth' => [
                'type' => 'apiKey',
                'in' => 'header',
                'name' => 'X-API-Key',
                'description' => 'API Key authentication via X-API-Key header (e.g. driver-api-secret-key-2026, attender-api-secret-key-2026, student-details-api-secret-key-2026, dashboard-api-secret-key-2026, system-admin-api-key-2026)',
            ],
        ],
    ],
];

function extract_swagger_meta($file) {
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

$paths = [];
$added_tags = [];

$api_controller_dir = __DIR__ . '/application/controllers/api';
foreach (glob($api_controller_dir . '/*.php') as $file) {
    $class_name = basename($file, '.php');
    if (strtolower($class_name) === 'base') {
        continue;
    }

    $meta = extract_swagger_meta($file);
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
        $path = preg_replace('#^/api/#', '/', $raw_path);
        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

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

$swagger['paths'] = $paths;

// Write JSON
$json_file = __DIR__ . '/swagger.json';
$json_content = json_encode($swagger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($json_file, $json_content);
echo "Successfully generated Swagger JSON at: " . $json_file . "\n";

// Function to dump array to simple YAML
function array_to_yaml($data, $indent = 0) {
    $yaml = '';
    $prefix = str_repeat('  ', $indent);
    $is_assoc = false;

    if (is_array($data)) {
        $keys = array_keys($data);
        $is_assoc = array_keys($keys) !== $keys;
    }

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if (empty($value)) {
                if ($is_assoc) {
                    $yaml .= "{$prefix}{$key}: {}\n";
                } else {
                    $yaml .= "{$prefix}- {}\n";
                }
            } else {
                if ($is_assoc) {
                    $yaml .= "{$prefix}{$key}:\n" . array_to_yaml($value, $indent + 1);
                } else {
                    $sub_yaml = array_to_yaml($value, $indent + 1);
                    $lines = explode("\n", trim($sub_yaml));
                    $first = true;
                    foreach ($lines as $line) {
                        if (empty($line)) continue;
                        if ($first) {
                            $yaml .= "{$prefix}- " . ltrim($line) . "\n";
                            $first = false;
                        } else {
                            $yaml .= "{$prefix}  " . substr($line, $indent * 2 + 2) . "\n";
                        }
                    }
                }
            }
        } else {
            if (is_bool($value)) {
                $str_val = $value ? 'true' : 'false';
            } elseif (is_null($value)) {
                $str_val = 'null';
            } elseif (is_numeric($value)) {
                $str_val = (string)$value;
            } else {
                if (preg_match('/[:\#\[\]\{\}\,\&\*\?\|\-\<\>\=\!\%\@\`]/', $value) || strpos($value, "\n") !== false) {
                    $str_val = '"' . addcslashes($value, '"\\') . '"';
                } else {
                    $str_val = $value;
                }
            }

            if ($is_assoc) {
                $yaml .= "{$prefix}{$key}: {$str_val}\n";
            } else {
                $yaml .= "{$prefix}- {$str_val}\n";
            }
        }
    }
    return $yaml;
}

$yaml_file = __DIR__ . '/swagger.yaml';
$yaml_content = array_to_yaml($swagger);
file_put_contents($yaml_file, $yaml_content);
echo "Successfully generated Swagger YAML at: " . $yaml_file . "\n";
