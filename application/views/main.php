<div class="col-sm-9 col-md-10 col-lg-12 main">
		
	<h2 class="form-signin-heading heading">Скачивание</h2>
		
	<?php if (!empty($error)) { ?>
		<div class="alert alert-dismissable alert-danger">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<?php echo $error;?>
		</div>
	<?php } ?>
		
	<?php if (!empty($success)) { ?>
		<div class="alert alert-dismissable alert-success">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<?php echo $success;?>
		</div>
	<?php } ?>
		
	<br>
	
	<div>
		<div>
			Сайт: <input id="site" type='url' class="form-control" value='' autocomplete='on' maxlength='150' required placeholder="http://site.com/">
			<br>
			<button id="get_manga_info" class='btn btn-primary'>Инфо</button>
		</div>
		
		<br><br>
		
		<div>
			Папка: <input id="folder" type='url' class="form-control" value='' autocomplete='on' maxlength='150' placeholder="D:\manga">
			<br>
			<button id="get_manga_folder" class='btn btn-primary'>Найти</button>
		</div>
	</div>

	<br><br><br>
	
	<div>
		<button id="download_manga_chapter_image_list" class='btn btn-primary'>Получить списки изображений</button>
	</div>
	
	<br><br>
	
	<div>
		<button id="download_manga_chapter_list" class='btn btn-primary'>Сохранить главы (изображения)</button>
	</div>
	
	<br><br>
	
	<table id="manga" class='table'>
		<tr>
			<td>Описание</td>
			<td><input type='checkbox' id="checked_all_checkbox_download" value='1'>&nbsp;Главы</td>
			<td>Действие</td>
			<td align='right'>Наличие</td>
		</tr>
		<tr>
			<td class='manga'></td>
			<td class='chapter' colspan='3'></td>
		</tr>
	</table>

	<div id="" class=''></div>
</div>

<script type='text/javascript'>
	// Очищение таблицы манги
	function clear() {
		$('#manga').find('td.manga, td.chapter').empty();
	}
	
	$(document).ready(function() {
		// список доступных сайтов
		var sites = ['http://readmanga.me', 'http://adultmanga.ru'];
		// список глав и ссылок на картинки
		var image_list = {};
		
		(function() {
			var text = "<p>Поддерживаются сайты: "+sites.join(', ')+"</p>";
			$('h2.heading').after(text);
		})();
		
		// Инфо о манге
		$('#get_manga_info').on('click', function() {
			clear();
			$('.main div.alert').remove();
			
			var url = $("#site").val();
			var folder = $('#folder').val();
			
			var html = '';
			
			for(var key in sites) {
				if(url.indexOf(sites[key], 0) != -1) {
					var site = sites[key];
					break;
				}
			}
			
			if (!site) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Сайт не поддерживается/strong></div>");
				return;
			}
			
			$.ajax({
				url: "<?php echo $action['get_manga_info'];?>",
				data: {"url": url, 'folder':folder},
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] === 'true') {
						html = json['html'];
						
						if (!json['img']) {
							var img = $(html).find('div.subject-cower #slider a').eq(0).find('img').eq(0).attr('src');
							if (!img) {
								var img = $(html).find('div.subject-cower').eq(0).find('img').eq(0).attr('src');
							}
						} else {
							var img = "data:image/"+json['exception']+";base64, "+json['img'];
						}
						
						if (!json['description']) {
							var description = $(html).find('div.manga-description').eq(0).text();
						} else {
							var description = json['description'];
						}
						
						$("#manga td.manga").append("<img class='image' src='"+img+"'>");
						$("#manga td.manga").append("<br><br><div class='description'>" + description + "</div>");
						$("#manga td.manga").append("<button class='save_description btn btn-primary'>Сохранить</button>");
						
						var tr = $(html).find('div.chapters-link table.cTable tr');
						if (tr.length > 1) {
							var a_array = [];

							for(var i=(tr.length-1); i>0; i--) {
								var href = $(tr).eq(i).find('td').eq(1).find('a').eq(0).attr('href');
								var text = $(tr).eq(i).find('td').eq(1).find('a').eq(0).text();
								var a = "<div class='chapter_div'>";
								a += "<table class='table'>";
									a += "<tr>";
										a += "<td><input type='checkbox' class='checkbox_download'></td>";
										a += "<td><a href='javascript:void(0);' data-href='"+site+href+"' class='chapter_link'>"+text+"</a></td>";
										a += "<td><a href='javascript:void(0);' data-href='"+site+href+"' class='download'>Скачать</a></td>";
										a += "<td><span class='count'>-</span></td>";
										a += "<td><span class='folder'></span></td>"
									a += "</tr>";
								a += "</table>";
								a += "</div>";
								
								a_array.push(a);
							}
							a_array = a_array.join("<br><br>");
							$("#manga td.chapter").append(a_array);
						}
					} else if (json && json['success'] && json['success'] == 'false') {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
					}else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка парсинга</strong></div>");
					}
				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});
			
		});
		
		// Сохранение информации о манге
		$("#manga td.manga").on('click', 'button.save_description', function() {
			$('.main div.alert').remove();
			
			var folder = $('#folder').val();
			var description = $('#manga td.manga div.description').text().trim();
			var img = $('#manga td.manga').find('img.image').eq(0).attr('src');
			
			$.ajax({
				url: "<?php echo $action['save_manga_description'];?>",
				data: {"folder": folder, 'description':description, 'img':img},
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] === 'true') {
						$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Данные сохранены</strong></div>");
					} else if (json && json['success'] && json['success'] == 'false') {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
					}else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка сохранения данных</strong></div>");
					}
				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});
		});
		
		// Инфо о манге в каталоге
		$('#get_manga_folder').on('click', function() {
			$('.main div.alert').remove();
			$("#manga span.folder").empty();
			
			var folder = $("#folder").val();
			var html = '';
			
			$.ajax({
				url: "<?php echo $action['get_manga_folder'];?>",
				data: {"folder": folder},
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] === 'true') {
						var a_array = [];
						if (json['folder'].length) {
							var span = $('#manga div span.folder');
							var non_folder = [];
							for(var i=0; i<span.length; i++) {
								if (json['folder'][i]) {
									$(span).eq(i).text(json['folder'][i]['name']+' ('+json['folder'][i]['count']+')');
								} else {
									non_folder[i] = true;
								}
							}
							
							var error = [];
							for(var i=0; i<json['folder'].length; i++) {
								if (non_folder[i]) {
									error.push(json['folder'][i]['name']+' ('+json['folder'][i]['count']+')');
								}
							}
							
							if (error.length > 0) {
								error = error.join('<br><br>');
								$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+error+"</strong></div>");
							}
							
							
						} else {
							$('#manga div span.folder').eq(0).text('Данных нет');
						}
						
					} else if (json && json['success'] && json['success'] == 'false') {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
					}else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка поиска каталога</strong></div>");
					}
				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});
			
		});
		
		/* Получение списка картинок главы */
		$('#manga td.chapter').on('click', 'a.chapter_link', function() {
			var a = $(this);
			$('.main div.alert').remove();
			//$("#manga td.folder").empty();
			
			var url = $(this).attr('data-href');
			
			$.ajax({
				url: "<?php echo $action['get_image_list'];?>",
				data: {"url": url},
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] == 'true') {
						// TODO: плохо, но не знаю как иначе
						eval("var list = "+json['list']);
						
						image_list[url] = list;
						
						var len = 0;
						for(var key in list) {
							len++;
						}
						
						$('#manga td.chapter').find('a[data-href="'+url+'"]').parents('div').eq(0).find('span.count').text('('+len+')');
						
						$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Данные загружены</strong></div>");
					} else if (json && json['success'] && json['success'] == 'false') {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
					} else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка парсинга списка изображений</strong></div>");
					}
				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});
			
		});
		
		// Скачивание файлов
		$('#manga td.chapter').on('click', 'a.download', function() {
			
			$('.main div.alert').remove();
			
			var url = $(this).attr('data-href');
			if (!image_list[url]) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ссылки для скачивания изображений не найдены</strong></div>");
				return;
			}
			
			var data = url.split('/');
			if (!data[4] || !data[5]) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка парсинга ссылки</strong></div>");
				return;
			}
			
			var folder = $('#folder').val();
			if (!folder) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Каталог пустой</strong></div>");
				return;
			}

			$.ajax({
				url: "<?php echo $action['get_download_list'];?>",
				data: {"list": image_list[url], 'volume': data[4], 'chapter': data[5], 'folder': folder},
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] == 'true') {
						$('#get_manga_folder').click();
						$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Файлы загружены</strong></div>");
					} else if (json && json['success'] && json['success'] == 'false') {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
					} else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка поиска каталога</strong></div>");
					}
				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});
			
		});
		
		// Обновление ссылок на изображения глав
		$('#download_manga_chapter_image_list').on('click', function() {
			var ch = $('#manga input.checkbox_download');
			
			var list = [];
			for(var i=0; i<ch.length; i++) {
				if ($(ch).eq(i).attr('checked') === 'checked') {
					list.push(i);
				}
			}
			
			if (list.length == 0) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не выбраны главы</strong></div>");
				return;
			}
			
			recursionDownloadChapterLink(0, list);
		});
		
		
		function recursionDownloadChapterLink(index, list) {
			$('.main div.alert').remove();

			var url = $('#manga td.chapter a.download').eq(list[index]).attr('data-href');
			var next_index = index+1;

			if (image_list[url]) {
				var len = 0;
				for(var key in image_list[url]) {
					len++;
				}
				$('#manga td.chapter').find('a[data-href="'+url+'"]').parents('div').eq(0).find('span.count').text('('+len+')');
				
				if (list[next_index]) {
					recursionDownloadChapterLink(next_index, list);
					return;
				} else {
					$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Все данные загружены</strong></div>");
				}
			}
			
			$.ajax({
				url: "<?php echo $action['get_image_list'];?>",
				data: {"url": url},
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] == 'true') {
						// TODO: плохо, но не знаю как иначе
						eval("var list_link = "+json['list']);
						
						image_list[url] = list_link;
						
						var len = 0;
						for(var key in list_link) {
							len++;
						}
						
						$('#manga td.chapter').find('a[data-href="'+url+'"]').parents('div').eq(0).find('span.count').text('('+len+')');
						
						if (list[next_index]) {
							// если данные в кеше, то следующий запрос делаем сразу
							if (json['cache'] && (json['cache'] == 'true' || json['cache'] == true)) {
								recursionDownloadChapterLink(next_index, list);
							} else {
								setTimeout(function() {
									recursionDownloadChapterLink(next_index, list);
								}, 3000);
							}
						} else {
							$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Все данные глав загружены</strong></div>");
						}
						
					} else if (json && json['success'] && json['success'] == 'false') {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
					} else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка парсинга списка изображений</strong></div>");
					}
				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});
		}
		
		// Скачивание глав списком
		$('#download_manga_chapter_list').on('click', function() {
			var ch = $('#manga input.checkbox_download');
			
			var list = [];
			for(var i=0; i<ch.length; i++) {
				if ($(ch).eq(i).attr('checked') === 'checked') {
					list.push(i);
				}
			}
			
			if (list.length == 0) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не выбраны главы</strong></div>");
				return;
			}
			
			recursionDownloadImageList(0, list);
		});
		
		// Рекурсивное скачивание изображений по главам
		function recursionDownloadImageList(index, list) {
			$('.main div.alert').remove();
			var next_index = index+1;
			var url = $('#manga td.chapter a.download').eq(list[index]).attr('data-href');
			if (!image_list[url]) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ссылки для скачивания изображений не найдены</strong></div>");
				return;
			}
			
			var data = url.split('/');
			if (!data[4] || !data[5]) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка парсинга ссылки</strong></div>");
				return;
			}
			
			var folder = $('#folder').val();
			if (!folder) {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Каталог пустой</strong></div>");
				return;
			}

			$.ajax({
				url: "<?php echo $action['get_download_list'];?>",
				data: {"list": image_list[url], 'volume': data[4], 'chapter': data[5], 'folder': folder},
				timeout: 900000,
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] == 'true') {
						if (!json['message']) {
							$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Файлы загружены</strong></div>");
						} else {
							$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
						}
						
						if (!list[next_index]) {
							$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Файлы загружены</strong></div>");
						}
					} else if (json && json['success'] && json['success'] == 'false') {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+json['message']+"</strong></div>");
					} else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ошибка поиска каталога</strong></div>");
					}
					
					if (json && (!json['fatal'] || json['fatal'] != 'true') && list[next_index]) {
						setTimeout(function() {
							recursionDownloadImageList(next_index, list);
						}, 3000);
					}
					
				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});
		}
		
		// Выбор чекбокосов всех
		$('#checked_all_checkbox_download').on('change', function() {
			if ($(this).attr('checked') === 'checked') {
				$('#manga input.checkbox_download').attr('checked', 'checked');
			} else {
				$('#manga input.checkbox_download').removeAttr('checked');
			}
		});
		
	});
</script>
