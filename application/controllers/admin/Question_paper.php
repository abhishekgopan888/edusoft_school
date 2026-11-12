<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Question_paper extends Admin_Controller
{
    private $ai_api_url = 'http://43.205.138.36:8002/api/generate-questions';

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('question_paper_model', 'chapter_model', 'class_model', 'section_model', 'subject_model', 'setting_model'));
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'question_generator');
        $data['title'] = $this->lang->line('question_paper_generator');

        $data['classlist'] = $this->class_model->get();
        $data['subjectlist'] = $this->subject_model->get();
        $data['question_papers'] = $this->question_paper_model->get();
        $data['sch_setting'] = $this->setting_model->getSetting();

        $this->load->view('layout/header', $data);
        $this->load->view('admin/question_paper/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function generate()
    {
        header('Content-Type: application/json');

        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $subject_id = $this->input->post('subject_id');
        $chapter_ids = $this->input->post('chapter_ids');
        $question_count = intval($this->input->post('question_count'));
        $difficulty = $this->input->post('difficulty');
        $question_types = $this->input->post('question_types');
        $suggestions = $this->input->post('suggestions');

        if ($question_count <= 0) {
            $question_count = 10;
        }

        // Resolve names
        $class_name = '';
        if (!empty($class_id)) {
            $class_row = $this->db->select('class')->from('classes')->where('id', $class_id)->get()->row();
            if ($class_row) {
                $class_name = $class_row->class;
            }
        }

        $subject_name = '';
        if (!empty($subject_id)) {
            $sub_row = $this->db->select('name')->from('subjects')->where('id', $subject_id)->get()->row();
            if ($sub_row) {
                $subject_name = $sub_row->name;
            }
        }

        $chapter_names = array();
        if (!empty($chapter_ids) && is_array($chapter_ids)) {
            $this->db->select('chapter_name, chapter_no')->from('chapters')->where_in('id', $chapter_ids);
            $chap_res = $this->db->get()->result();
            foreach ($chap_res as $c) {
                $chapter_names[] = (!empty($c->chapter_no) ? $c->chapter_no . ': ' : '') . $c->chapter_name;
            }
        }

        $payload = array(
            'class_name' => $class_name,
            'subject_name' => $subject_name,
            'chapters' => $chapter_names,
            'question_count' => $question_count,
            'difficulty' => !empty($difficulty) ? $difficulty : 'Medium',
            'question_types' => !empty($question_types) ? $question_types : array('MCQ', 'Short', 'Long'),
            'suggestions' => $suggestions
        );

        $questions = $this->_fetch_from_api($payload);

        if (!empty($questions)) {
            echo json_encode(array(
                'status' => 'success',
                'source' => 'ai_endpoint',
                'ai_endpoint' => $this->ai_api_url,
                'count' => count($questions),
                'questions' => $questions
            ));
        } else {
            echo json_encode(array(
                'status' => 'fail',
                'message' => 'Failed to generate questions from AI endpoint (' . $this->ai_api_url . '). Please ensure the AI service is online and accessible.',
                'ai_endpoint' => $this->ai_api_url,
                'questions' => array()
            ));
        }
    }

    public function generate_single()
    {
        header('Content-Type: application/json');
        $class_id = $this->input->post('class_id');
        $subject_id = $this->input->post('subject_id');
        $chapter_name = $this->input->post('chapter');
        $question_type = $this->input->post('question_type');
        $difficulty = $this->input->post('difficulty');
        $question_no = intval($this->input->post('question_no'));

        // Resolve names
        $class_name = '';
        if (!empty($class_id)) {
            $class_row = $this->db->select('class')->from('classes')->where('id', $class_id)->get()->row();
            if ($class_row) {
                $class_name = $class_row->class;
            }
        }

        $subject_name = '';
        if (!empty($subject_id)) {
            $sub_row = $this->db->select('name')->from('subjects')->where('id', $subject_id)->get()->row();
            if ($sub_row) {
                $subject_name = $sub_row->name;
            }
        }

        $chapter_names = !empty($chapter_name) ? array($chapter_name) : array($subject_name ? $subject_name : 'General Concepts');

        $payload = array(
            'class_name' => $class_name,
            'subject_name' => $subject_name,
            'chapters' => $chapter_names,
            'question_count' => 1,
            'difficulty' => !empty($difficulty) && $difficulty != 'Mixed' ? $difficulty : 'Medium',
            'question_types' => !empty($question_type) ? array($question_type) : array('MCQ'),
            'suggestions' => ''
        );

        $questions = $this->_fetch_from_api($payload);
        if (!empty($questions)) {
            $q = $questions[0];
            $q['question_no'] = $question_no > 0 ? $question_no : 1;
            echo json_encode(array('status' => 'success', 'question' => $q));
        } else {
            echo json_encode(array(
                'status' => 'fail',
                'message' => 'Could not generate question from AI endpoint (' . $this->ai_api_url . ')'
            ));
        }
    }

    private function _fetch_from_api($payload)
    {
        $ch = curl_init($this->ai_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response_body = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        $questions = array();
        if ($http_code >= 200 && $http_code < 300 && !empty($response_body)) {
            $decoded = json_decode($response_body, true);
            if (is_array($decoded)) {
                if (isset($decoded['data']['questions']) && is_array($decoded['data']['questions'])) {
                    $questions = $this->_normalize_questions($decoded['data']['questions']);
                } elseif (isset($decoded['questions']) && is_array($decoded['questions'])) {
                    $questions = $this->_normalize_questions($decoded['questions']);
                } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
                    $questions = $this->_normalize_questions($decoded['data']);
                } elseif (isset($decoded[0])) {
                    $questions = $this->_normalize_questions($decoded);
                }
            }
        } else {
            log_message('error', 'AI Question Generator connection (URL: ' . $this->ai_api_url . '): HTTP ' . $http_code . ' ' . $curl_error);
        }

        return $questions;
    }

    private function _normalize_questions($raw_list)
    {
        $normalized = array();
        $q_num = 1;
        foreach ($raw_list as $item) {
            if (is_string($item)) {
                $normalized[] = array(
                    'question_no' => $q_num++,
                    'chapter' => '',
                    'question_type' => 'Descriptive',
                    'question_text' => $item,
                    'options' => array(),
                    'marks' => 2,
                    'answer_key' => '',
                    'step_marking' => array(),
                    'explanation' => '',
                    'difficulty' => 'Medium'
                );
            } elseif (is_array($item)) {
                $q_id = isset($item['id']) ? $item['id'] : (isset($item['question_no']) ? $item['question_no'] : $q_num++);
                $chapter = isset($item['chapter']) ? $item['chapter'] : (isset($item['chapter_name']) ? $item['chapter_name'] : '');
                $q_text = isset($item['question']) ? $item['question'] : (isset($item['question_text']) ? $item['question_text'] : (isset($item['text']) ? $item['text'] : ''));
                $q_type = isset($item['type']) ? $item['type'] : (isset($item['question_type']) ? $item['question_type'] : 'Short');
                $raw_opts = isset($item['options']) ? $item['options'] : (isset($item['choices']) ? $item['choices'] : (isset($item['alternatives']) ? $item['alternatives'] : array()));
                $marks = isset($item['marks']) ? $item['marks'] : (isset($item['mark']) ? $item['mark'] : ($q_type == 'MCQ' ? 1 : 3));
                $raw_ans = isset($item['correct_answer']) ? $item['correct_answer'] : (isset($item['answer_key']) ? $item['answer_key'] : (isset($item['answer']) ? $item['answer'] : (isset($item['solution']) ? $item['solution'] : '')));
                $step_marking = isset($item['step_marking']) ? $item['step_marking'] : (isset($item['steps']) ? $item['steps'] : array());
                $explanation = isset($item['explanation']) ? $item['explanation'] : (isset($item['rationale']) ? $item['rationale'] : '');
                $diff = isset($item['difficulty']) ? $item['difficulty'] : 'Medium';

                // Normalize options list for MCQs
                $cleaned_opts = array();
                if (is_string($raw_opts)) {
                    $json_opts = json_decode($raw_opts, true);
                    if (is_array($json_opts)) {
                        $raw_opts = $json_opts;
                    }
                }

                if (is_array($raw_opts)) {
                    foreach ($raw_opts as $k => $val) {
                        $opt_str = '';
                        if (is_string($val) || is_numeric($val)) {
                            $opt_str = (string)$val;
                        } elseif (is_array($val)) {
                            if (isset($val['text'])) {
                                $opt_str = (string)$val['text'];
                            } elseif (isset($val['option'])) {
                                $opt_str = (string)$val['option'];
                            } elseif (isset($val['value'])) {
                                $opt_str = (string)$val['value'];
                            } elseif (!empty($val)) {
                                $opt_str = (string)reset($val);
                            }
                        }
                        if ($opt_str !== '') {
                            $cleaned = preg_replace('/^[A-Za-z][\)\.\-]\s*/', '', trim($opt_str));
                            $cleaned_opts[] = !empty($cleaned) ? $cleaned : trim($opt_str);
                        }
                    }
                }

                if (!empty($cleaned_opts) && empty($item['type']) && empty($item['question_type'])) {
                    $q_type = 'MCQ';
                }

                $ans_text = '';
                if (is_array($raw_ans)) {
                    $ans_text = implode(', ', array_map('strval', $raw_ans));
                } else {
                    $ans_text = (string)$raw_ans;
                }

                $normalized[] = array(
                    'question_no' => $q_id,
                    'chapter' => $chapter,
                    'question_type' => $q_type,
                    'question_text' => $q_text,
                    'options' => $cleaned_opts,
                    'marks' => floatval($marks),
                    'answer_key' => $ans_text,
                    'step_marking' => is_array($step_marking) ? $step_marking : array(),
                    'explanation' => $explanation,
                    'difficulty' => $diff
                );
            }
        }
        return $normalized;
    }

    public function save()
    {
        header('Content-Type: application/json');

        $title = $this->input->post('title');
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $subject_id = $this->input->post('subject_id');
        $chapter_ids = $this->input->post('chapter_ids');
        $total_marks = $this->input->post('total_marks');
        $duration_minutes = $this->input->post('duration_minutes');
        $instructions = $this->input->post('instructions');
        $ai_suggestions = $this->input->post('suggestions');
        $ai_endpoint_used = $this->input->post('ai_endpoint_url');
        $questions_json = $this->input->post('questions_json');

        if (empty($title) || empty($class_id) || empty($subject_id)) {
            echo json_encode(array('status' => 'fail', 'message' => 'Please provide Title, Class, and Subject'));
            return;
        }

        $questions_array = json_decode($questions_json, true);
        if (empty($questions_array)) {
            echo json_encode(array('status' => 'fail', 'message' => 'No questions provided to save'));
            return;
        }

        $chapter_ids_str = is_array($chapter_ids) ? implode(',', $chapter_ids) : $chapter_ids;

        $paper_data = array(
            'title' => $title,
            'class_id' => $class_id,
            'section_id' => !empty($section_id) ? $section_id : null,
            'subject_id' => $subject_id,
            'chapter_ids' => $chapter_ids_str,
            'total_marks' => !empty($total_marks) ? intval($total_marks) : 100,
            'duration_minutes' => !empty($duration_minutes) ? intval($duration_minutes) : 60,
            'instructions' => $instructions,
            'ai_suggestions' => $ai_suggestions,
            'ai_endpoint_used' => $ai_endpoint_used
        );

        $inserted_id = $this->question_paper_model->add($paper_data, $questions_array);

        if ($inserted_id) {
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Question Paper saved successfully!',
                'id' => $inserted_id
            ));
        } else {
            echo json_encode(array('status' => 'fail', 'message' => 'Failed to save question paper'));
        }
    }

    public function view_modal($id)
    {
        header('Content-Type: application/json');
        $paper = $this->question_paper_model->get($id);
        if ($paper) {
            echo json_encode(array('status' => 'success', 'data' => $paper));
        } else {
            echo json_encode(array('status' => 'fail', 'message' => 'Not found'));
        }
    }

    public function print_paper($id)
    {
        $data['paper'] = $this->question_paper_model->get($id);
        if (!$data['paper']) {
            redirect('admin/question_paper');
        }
        $data['sch_setting'] = $this->sch_setting_detail;
        $data['setting'] = $this->setting_model->getSetting();
        $this->load->view('admin/question_paper/print_paper', $data);
    }

    public function delete($id)
    {
        $this->question_paper_model->delete($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/question_paper');
    }
}
