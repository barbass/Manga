<div class="col-sm-9 col-md-10 col-lg-12 main">
		
	<h2 class="form-signin-heading">Скачивание</h2>
		
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

	<br><br><br>
		
	<table id="manga" class='table'>
		<tr>
			<td class='manga'></td>
			<td class='chapter'></td>
			<td class='folder'></td>
		</tr>
	</table>

	<div id="" class=''></div>
</div>

<script type='text/javascript'>
	// Очищение таблицы манги
	function clear() {
		$('#manga').find('td.manga, td.chapter, td.folder').empty();
	}
	
	$(document).ready(function() {
		// список доступных сайтов
		var sites = ['http://readmanga.me', 'http://adultmanga.ru'];
		// список глав и ссылок на картинки
		var image_list = {};
		
		// Инфо о манге
		$('#get_manga_info').on('click', function() {
			clear();
			$('.main div.alert').remove();
			
			var url = $("#site").val();
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
				data: {"url": url},
				type: "POST",
				dataType: 'json',
				success: function(json) {
					if (json && json['success'] && json['success'] === 'true') {
						html = json['html'];
						var img = $(html).find('div.subject-cower #slider a').eq(0).find('img');
						var description = $(html).find('div.manga-description').eq(0).text();
						$("#manga td.manga").append(img);
						$("#manga td.manga").append("<br><br>"  + description);
						
						var tr = $(html).find('div.chapters-link table.cTable tr');
						if (tr.length > 1) {
							var a_array = [];
							for(var i=(tr.length -1 ); i>0; i--) {
								var href = $(tr).eq(i).find('td').eq(1).find('a').eq(0).attr('href');
								var text = $(tr).eq(i).find('td').eq(1).find('a').eq(0).text();
								var a= "<div class='chapter_div'>";
								a += "<a href='javascript:void(0);' data-href='"+site+href+"' class='chapter_link'>"+text+"</a>";
								a += "<a href='javascript:void(0);' data-href='"+site+href+"' class='download'>Скачать</a>";
								a = "</div>";
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
		
		// Инфо о манге в каталоге
		$('#get_manga_folder').on('click', function() {
			$('.main div.alert').remove();
			$("#manga td.folder").empty();
			
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
							for(var i=0; i<json['folder'].length; i++) {
								a_array.push(json['folder'][i]['name']+' ('+json['folder'][i]['count']+')');
							}
							a_array = a_array.join('<br><br>');
							$("#manga td.folder").append(a_array);
						} else {
							$("#manga td.folder").html('Данных нет');
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
		
		// Получение списка картинок главы
		$('#manga td.chapter').on('click', 'a.chapter_link', function() {
			
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
		
	});
</script>
