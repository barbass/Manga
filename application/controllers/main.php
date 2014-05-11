<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
	// Переменная нахождения данных в кеше
	protected $in_cache = false;
	
	public function __contruct() {
		
	}

	public function index() {
		$data['action'] = array(
			'get_manga_info' => base_url('main/getMangaInfo'),
			'get_manga_folder' => base_url('main/getMangaFolder'),
			'get_image_list' => base_url('main/getImageList'),
			'get_download_list' => base_url('main/downloadImage'),
			'save_manga_description' => base_url('main/saveMangaDescription'),
		);
		
		$this->load->view('header');
		$this->load->view('main', $data);
		$this->load->view('footer');
	}
	
	/*
	 * Получение информации по манге
	 * */
	public function getMangaInfo() {
		$url = $this->input->get_post('url');
		$folder = $this->input->get_post('folder', '');
		$json = array();
		
		try {
			
			if (!empty($folder)) {
				/*$description = read_file($folder.'main.txt');
				if (!empty($description)) {
					$json['description'] = $description;
				}*/
				
				/*foreach(array('png', 'gif', 'jpg', 'jpeg') as $key=>$value) {
					if (file_exists($folder.'/'.'main.'.$value)) {
						//$image = readfile($folder.'\\'.'main.'.$value);
						//$image = base64_encode($image);
						$exception = $value;
					}
				}
				
				if (!empty($image)) {
					$json['img'] = $image;
					$json['exception'] = $exception;
				}*/
			}
			
			$html = $this->getContents($url, true);

			if (!empty($html)) {
				$json['html'] = $html;
				$json['success'] = 'true';
			} else {
				$json['success'] = 'false';
				$json['message'] = 'Нет данных';
			}
			
		} catch (Exception $e) {
			$json['success'] = 'false';
			$json['message'] = $e->getMessage();
		}
		
		$this->output->set_content_type('text/json')->set_output(json_encode($json));
	}
	
	public function getMangaFolder() {
		$folder = $this->input->get_post('folder');
		if (!is_dir($folder)) {
			$json['success'] = 'false';
			$json['message'] = 'Папка не найдена';
		} else {
			$json['success'] = 'true';
			$json['folder'] = array();
			if ($handle = opendir($folder)) {
				$i = 0;
				while (false !== ($entry = readdir($handle))) {
					if (is_file($folder.'/'.$entry) || $entry == '.' || $entry == '..') {
						continue;
					}
					
					if ($handle2 = opendir($folder.'/'.$entry)) {
						while (false !== ($entry2 = readdir($handle2))) {
							if ($entry2 == '.' || $entry2 == '..') {
								continue;
							}
							
							$json['folder'][$i]['name'] = $entry.'/'.$entry2;
							$files = get_dir_file_info($folder.'/'.$entry.'/'.$entry2);
							$json['folder'][$i]['count'] = count($files);
							$i++;
						}
					}
	
				}
				
				closedir($handle);
			}
		}
		
		$this->output->set_content_type('text/json')->set_output(json_encode($json));
	}
	
	/*
	 * Список изображений (из js парсим)
	 * */
	public function getImageList() {
		$url = $this->input->get_post('url');
		$json = array();
		
		try {
			// ?mature=1 добавляем, чтобы не спршивал согласие на жестокость сцен
			$html = $this->getContents($url.'/?mature=1', true);

			if (!empty($html)) {
				$data = explode('var pictures = ', $html, 2);
				if (empty($data[1])) {
					$json['success'] = 'false';
					$json['message'] = 'Нет данных (pictures)';
				} else {
					$data = explode("var prevLink", $data[1], 2);
					if (empty($data[0])) {
						$json['success'] = 'false';
						$json['message'] = 'Нет данных (prevLink)';
					} else {
						$data = trim($data[0]);
						// удаляем ;
						$data = substr_replace($data,'', -1, 1);
						
						$json['list'] = $data;
						$json['success'] = 'true';
						$json['cache'] = $this->in_cache;
					}
				}
				
			} else {
				$json['success'] = 'false';
				$json['message'] = 'Нет данных';
			}
			
		} catch (Exception $e) {
			$json['success'] = 'false';
			$json['message'] = $e->getMessage();
		}
		
		$this->output->set_content_type('text/json')->set_output(json_encode($json));
	}
	
	/* 
	 * Загрузка изображений
	 * */
	public function downloadImage() {
		// для больших манг делаем 15 минут время скрипта
		set_time_limit(900); 
		
		$json = array();
		$list = $this->input->get_post('list');
		$volume = $this->input->get_post('volume', 'vol1');
		$chapter = $this->input->get_post('chapter', '1');
		$folder = $this->input->get_post('folder', 'default');
		
		if (!is_dir($folder)) {
			mkdir ($folder);
		}
		if (!is_dir($folder.'/'.$volume)) {
			mkdir ($folder.'/'.$volume);
		}
		if (!is_dir($folder.'/'.$volume.'/'.$chapter)) {
			mkdir ($folder.'/'.$volume.'/'.$chapter);
		}
		
		$dir = $folder.'/'.$volume.'/'.$chapter;
		
		// Грузим данные по циклу
		$i = 0;
		foreach($list as $l) {
			$image_array = explode('/', $l['url']);
			$image = end($image_array);
			
			// или грузить все, а потом сравнивать по md5?
			if (file_exists($dir.'/'.$image)) {
				continue;
			}
			
			try {
				$data = $this->getHtml($l['url']);
				if (empty($data)) {
					$json['success'] = 'false';
					$json['message'] = 'Ошибка загрузки изображения'.' ('.$l['url'].')';
					continue;
				}
				
				write_file($dir.'/'.$image, $data);
			} catch(Exception $e) {
				$json['success'] = 'false';
				$json['message'] = 'Ошибка загрузки изображения';
				continue;
			}
			
			// заставляем скрипт заснуть на N секунд, чтобы не было DDOS и ресурсы сервера не загрузить
			if ($i > 1) {
				$i = 0;
				sleep(5);
			}
			
			$i++;
		}
		
		if (empty($json)) {
			$json['success'] = 'true';
			$json['message'] = 'Данные сохранены'.' ('.$volume.' - '.$chapter.')';
		}
		
		$this->output->set_content_type('text/json')->set_output(json_encode($json));
	}

	/*
	 * Сохранение описания манги
	 * */
	public function saveMangaDescription() {
		$json = array();
		$img = $this->input->get_post('img', '');
		$description = $this->input->get_post('description', '');
		$folder = $this->input->get_post('folder', 'default');
		
		if (!is_dir($folder)) {
			mkdir($folder);
		}
		
		if (!empty($description) || !is_dir($folder.'/'.'main.txt')) {
			write_file($folder.'/'.'main.txt', $description);
		}
		
		if (!empty($img)) {
			$image_array = explode('/', $img);
			$image = end($image_array);
			
			$data = $this->getHtml($img);
			write_file($folder.'/'.$image, $data);
			
			$extension_image_array = explode('.', $image);
			$extension_image = end($extension_image_array);

			if (in_array($extension_image, array('png', 'gif', 'jpg', 'jpeg'))) {
				rename($folder.'/'.$image, $folder.'/'.'main.'.$extension_image);
			} else {
				unlink($folder.'/'.$image);
			}
		}
		
		$json['success'] = 'true';
		$json['message'] = 'Данные сохранены';
		
		$this->output->set_content_type('text/json')->set_output(json_encode($json));
	}

	/*
	 * Загрузка страницы
	 * TODO: в model по факту надо перенести
	 * TODO: попробовать заменить на cUrl
	 * */
	private function getContents($url, $cache = false, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true) {
		$this->load->driver('cache', array('adapter' => 'file'));
		
		$filename = md5($url);
		if (!$contents = $this->cache->get($filename)) {
			$this->in_cache = false;
			
			$contents = file_get_contents($url, $use_include_path, $context, $offset);
			if (!$contents || empty($contents) || strlen($contents) > 600000) {
				throw new Exception('Ошибка загрузки страницы');
				return false;
			}
			// сохраняем кеш на 24 часа
			if ($cache === true) {
				$this->cache->save($filename, $contents, 86400);
			}
		} else {
			$this->in_cache = true;
		}
		
		if (!$contents) {
			throw new Exception('Ошибка загрузки страницы');
			return;
		}
		
		return $contents;
	}
	
	/*
	 * Скачивание данных
	 * Доп. функция для работы через cUrl
	 * */
	private function getHtml($url) {
		ob_clean();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'text/html; encoding=utf-8'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		return $result;
	}
	
}
