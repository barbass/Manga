<div class="col-sm-9 col-md-10 col-lg-12 main">

	<h2 class="form-signin-heading heading">Поиск новых глав</h2>

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
		<div id='site_list'>
			<table class='table'>
				<tr><th>Сайты:</th></tr>
				<?php foreach($site_list as $s) { ?>
					<tr>
						<td>
							<?php echo $s;?>
							<input type='hidden' value='<?php echo $s;?>' class='site'>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>

		<div id='manga_list'>
			<table class='table'>
				<tr><th>Список текущих манг:</th></tr>
				<?php foreach($manga_list as $m) { ?>
					<tr>
						<td>
							<?php echo $m;?>
							<input type='hidden' value='<?php echo $m;?>' class='manga'>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>

		<div>
			<b>Кол-во страниц для обработки:</b>
			<input type='number' min='1' max='10' value='1' class='form-control input-sm' id='pages'>
		</div>
	</div>

	<div class='block'>
		<button class='btn btn-primary' id='search'>
			<span class="glyphicon glyphicon-search"></span>&nbsp;Поиск
		</button>
	</div>

	<div class='block'>
		<button class="btn btn-primary" id="button_ajax">
			<span class="glyphicon glyphicon-ban-circle"></span>&nbsp;Остановить ajax-запросы
		</button>
	</div>

	<div class='block'>
		<table class='table' id='new_manga_table'>
			<thead>
				<tr>
					<th>Найденные главы:</th>
					<th></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>

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

	// Очищение таблицы манги
	function clear() {
		$('#new_manga_table tbody tr').empty();
	}

	$(document).ready(function() {
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

		// Инфо о манге
		$('#search').on('click', function() {
			clear();
			$('.main div.alert').remove();

			var manga_list = JSON.parse('<?php echo json_encode($manga_list);?>');

			var pages = $('#pages').val();

			var inputs = $('#site_list input.site');
			var sites = [];
			for(var i=0; i<inputs.length; i++) {
				sites.push($(inputs).eq(i).val());
			}

			getHtml(sites, pages, 0, 1, manga_list);
		});

		/**
		 * Получение страниц по циклу
		 * @param array
		 * @param int
		 * @param int
		 * @param int
		 */
		function getHtml(sites, pages, site_index, page, manga_list) {
			var max_limit = 30;
			var url = sites[site_index];
			if (page > 1) {
				url += "?max="+max_limit+"&offset="+(max_limit*(page-1))
			}

			$.ajax({
				url: "<?php echo $action['get_html'];?>",
				data: {"url": url},
				type: "POST",
				dataType: 'html',
				success: function(html) {
					var result = searchManga(manga_list, sites[site_index], html);

					for(var key in result) {
						var tr = "<tr>";
							if (result[key]['image']) {
								tr += "<td><img width='100' height='120' src='"+result[key]['image']+"' /></td>";
							} else {
								tr += "<td></td>";
							}
							tr += "<td><a target='_blank' href='"+result[key]['href']+"'>"+result[key]['text']+"</a></td>";
						tr += "</tr>";

						$('#new_manga_table tbody').append(tr);
					}

					if ((page+1) > pages &&
						(site_index+1) >= sites.length
					) {
						$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Поиск окончен</strong></div>");
						return;
					}

					// если страницы не закончены, прогоняем первый сайт
					if ((page+1) <= pages) {
						getHtml(sites, pages, site_index, (page+1), manga_list);
					} else if ((site_index+1) < sites.length) {
						getHtml(sites, pages, (site_index+1), 1, manga_list);
					}

				},
				error: function() {
					$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
				},
			});

		}

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

	});

	/**
	 * Поиск глав
	 * @param array Список нужных глав
	 * @param string Сайт
	 * @param string Html-страница
	 */
	function searchManga(manga_list, site, html) {
		var manga_l = [];
		var tmp = site.split('/');
		var site_name = tmp[tmp.length-1];
		var result = [];

		// поиск манг для сайта
		for(var i=0; i<manga_list.length; i++) {
			if (manga_list[i].indexOf(site_name) == -1) {
				continue;
			}

			// оставляем только название манги
			var tmp = manga_list[i].split('/');
			manga_l.push(tmp[tmp.length-1]);
		}

		if (manga_l.length == 0) {
			return {};
		}

		// просматриваем ссылки на главы
		var trs = $(html).find('table.newChapters tr');

		for(var i=0; i<trs.length; i++) {
			var a = $(trs).eq(i).find('a');
			if (a.length == 0) {
				continue;
			}

			for(var j=0; j<manga_l.length; j++) {
				if ($(a).eq(0).attr('href').indexOf(manga_l[j]) > -1) {
					result.push({
						'text': $(a).eq(0).text(),
						'href': site+$(a).eq(0).attr('href'),
						'image': ($(a).eq(1)) ? $(a).eq(1).attr('rel') : null,
					});
				}
			}
		}

		return result;
	}
</script>
