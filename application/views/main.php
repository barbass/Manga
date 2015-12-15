<div class="col-sm-9 col-md-10 col-lg-12 main">

	<h2 class="form-signin-heading heading">Скачивание манги</h2>

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

	<div id='ajaxLoading' class='hide' title='Ajax-загрузка...'>
		<div class="progress progress-striped active">
			<div class="progress-bar"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
				<span class="sr-only">100% Complete</span>
			</div>
		</div>
		<span>Ajax-загрузка...</span>
	</div>

	<div class='block'>
		<div>
			Сайт: <input id="site" type='url' class="form-control" value='' autocomplete='on' maxlength='150' required placeholder="http://site.com/">
			<br>
			<button id="get_manga_info" class='btn btn-primary'>
				<span class="glyphicon glyphicon-eye-open"></span>
				Инфо
			</button>
		</div>

		<br>

		<div>
			Папка: <input id="folder" type='url' class="form-control" value='D:\manga\' autocomplete='on' maxlength='150' placeholder="D:\manga">
			<br>
			<button id="get_manga_folder" class='btn btn-primary'>
				<span class="glyphicon glyphicon-search"></span>
				Найти
			</button>
		</div>
	</div>

	<div class='block'>
		<button id="download_manga_chapter_image_list" class='btn btn-primary'>
			<span class="glyphicon glyphicon-list-alt"></span>
			Получить списки изображений
		</button>
		&nbsp;&nbsp;&nbsp;
		<button id="download_manga_chapter_list" class='btn btn-primary'>
			<span class="glyphicon glyphicon-download-alt"></span>
			Сохранить главы (изображения)
		</button>
	</div>

	<div class='block'>
		<textarea id='select_chapter' class='form-control' placeholder='Укажите главы через , или -. Пример: 1/1,1/2,2/3-3/10'></textarea>
		<br>
		<button id="button_select_chapter" class='btn btn-primary'>
			<span class="glyphicon glyphicon-th-list"></span>
			Выбрать главы
		</button>
	</div>

	<div class='block'>
		<button id="button_ajax" class='btn btn-primary'>
			<span class="glyphicon glyphicon-ban-circle"></span>&nbsp;Остановить ajax-запросы
		</button>
	</div>

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

<a href='javascript:void(0);' id="back_top">Наверх</a>

<script type='text/javascript'>
	var ajax_allowed = true;

	$.ajaxPrefilter(function(options, originalOptions, jqXHR){
		if(!ajax_allowed) {
			jqXHR.abort();
			$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ajax-запросы запрещены</strong></div>");
		}
	});

	var manga_obj = new Manga({
			'url_list': {
				'get_manga_info': "<?php echo $action['get_manga_info'];?>",
				'save_manga_description': "<?php echo $action['save_manga_description'];?>",
				'get_manga_folder': "<?php echo $action['get_manga_folder'];?>",
				'get_image_list': "<?php echo $action['get_image_list'];?>",
				'get_download_list': "<?php echo $action['get_download_list'];?>",
				'get_image_list': "<?php echo $action['get_image_list'];?>",
				'get_download_list': "<?php echo $action['get_download_list'];?>",
			},
		});

	$(document).ready(function() {
		(function() {
			var sites = manga_obj.getAccessSiteList();
			var text = "<p>Поддерживаются сайты: "+sites.join(', ')+"</p>";
			$('h2.heading').after(text);
		})();

		// при начале ajax-запросов
		$(document).ajaxStart(function() {
			$(ajaxLoading).removeClass('hide');
		});
		// при окончании ajax-запросов
		$(document).ajaxStop(function() {
			$(ajaxLoading).addClass('hide');
		});

		// Запрещение/разрешение ajax-запросов
		$('#button_ajax').on('click', function() {
			$('.main div.alert').remove();

			if (ajax_allowed) {
				ajax_allowed = false;
				$(this).html('<span class="glyphicon glyphicon-ok-circle"></span>&nbsp;Разрешить ajax-запросы');
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ajax-запросы остановлены</strong></div>");
			} else {
				ajax_allowed = true;
				$(this).html('<span class="glyphicon glyphicon-ban-circle"></span>&nbsp;Остановить ajax-запросы');
					$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Ajax-запросы разрешены</strong></div>");
			}

		});

		// Кнопка Наверх
		$(window).scroll(function() {
			if ($(this).scrollTop() > 400) {
				$('#back_top').fadeIn();
			} else {
				$('#back_top').fadeOut();
			}
		});

		$('#back_top').on('click', function() {
			$('body,html').animate({scrollTop: 0}, 800);
			return false;
		});

		// Выбор чекбоксов всех
		$('#checked_all_checkbox_download').on('change', function() {
			if ($(this).attr('checked') === 'checked') {
				$('#manga input.checkbox_download').attr('checked', 'checked');
			} else {
				$('#manga input.checkbox_download').removeAttr('checked');
			}
		});

		// Выбор указанных глав
		$('#button_select_chapter').on('click', function() {
			// TODO: обработка глав
			$('#manga input.checkbox_download').removeAttr('checked');

			// формат: 1/1, 1/2, 1/3-2/1
			var text = $('#select_chapter').val();
			var pre_chapters = text.split(',');

			for(var key in pre_chapters) {
				pre_chapters[key] = pre_chapters[key].trim();

				var tere = pre_chapters[key].match('-');
				// если 1/1
				if (tere == null) {
					$('#manga .chapter .chapter_link[data-href $= "vol'+pre_chapters[key]+'"]').parents('tr').eq(0).find('input.checkbox_download').attr('checked', 'checked');
				} else {
					// парсим границы
					var border = pre_chapters[key].split('-');
					if (!border[0] || !border[1]) {
						continue;
					} else {
						border[0] = border[0].trim();
						var start = border[0].split('/');
						// если указано без главы (1)
						if (!start[1]) {
							start[1] = -1;
						}

						border[1] = border[1].trim();
						var end = border[1].split('/');
						// если указано без главы (1)
						if (!end[1]) {
							end[1] = -1;
						}

						var vol_start = parseInt(start[0]);
						var chapter_start = parseInt(start[1]);
						var vol_end = parseInt(end[0]);
						var chapter_end = parseInt(end[1]);

						for(var i = vol_start; i <= vol_end; i++) {
							// если не указаны главы, то ищем сразу по томам
							if ((i != vol_start && i != vol_end) ||
								(i == vol_end && chapter_end == -1) ||
								(i == vol_start && chapter_start == -1)
							) {
								var a_chapter = $('#manga .chapter .chapter_link[data-href *= "vol'+i+'/"]');

								$(a_chapter).each(function(index, a) {
									$(a).parents('tr').eq(0).find('input.checkbox_download').attr('checked', 'checked');
								});

								continue;
							}

							// если указаны главы
							var a_chapter = $('#manga .chapter .chapter_link[data-href *= "vol'+i+'/"]');
							//Пробегаем по ссылке тома, сравниваем номер главы с границами
							$(a_chapter).each(function(index, a) {
								var href = $(a).attr('data-href');
								var href = href.split('vol'+i+'/');
								if (!href[1]) {
									href[1] = -1;
								}
								var ch = parseInt(href[1]);
								// если глава попадает под границы, то отмечаем
								if ((i == vol_start && ch >= chapter_start) ||
									(i == vol_end && ch <= chapter_end)
								) {
									$(a).parents('tr').eq(0).find('input.checkbox_download').attr('checked', 'checked');
								}

							});
						}

					}
				}
			}

		});

		// --------------------------------------------------------------------------- //

		// Инфо о манге
		$('#get_manga_info').on('click', function() {
			manga_obj.getMangaInfo();
		});

		// Сохранение информации о манге
		$("#manga td.manga").on('click', 'button.save_description', function() {
			manga_obj.saveMangaInfo();
		});

		// Инфо о манге в каталоге
		$('#get_manga_folder').on('click', function() {
			manga_obj.getMangaFromFolder();
		});

		// Получение списка картинок главы
		$('#manga td.chapter').on('click', 'a.chapter_link', function() {
			var url = $(this).attr('data-href');
			manga_obj.getImageListFromChapter(url);
		});

		// Скачивание файлов
		$('#manga td.chapter').on('click', 'a.download', function() {
			var url = $(this).attr('data-href');
			manga_obj.downloadImageListFromChapter(url);
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

			manga_obj.recursionDownloadChapterLink(0, list);
		});

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

			manga_obj.recursionDownloadImageList(0, list);
		});

	});

</script>
