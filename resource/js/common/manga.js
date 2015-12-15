/**
 * Обработка и скачивание манги с сайтов readmanga и mintmanga(бывший adultmanga)
 * @date 2015-12-15 11:49
 * @author barbass1025
 */
function Manga(options) {
	this.init(options);
}
Manga.prototype = {
	sites: new Array(
		'http://readmanga.me',
		'http://mintmanga.com'
	),

	/**
	 * Список url-ов
	 * @var object
	 */
	url_list: {},

	/**
	 * Список картинок по главам
	 * @var array
	 */
	image_list: [],

	init: function(options) {
		if (options['url_list']) {
			for(var key in options['url_list']) {
				this.url_list[key] = options['url_list'][key];
			}
		}
	},

	/**
	 * Получение информации о манге
	 */
	getMangaInfo: function() {
		this.clear();

		$('.main div.alert').remove();

		var url = $("#site").val();
		var folder = $('#folder').val();

		var html = '';

		for(var key in this.sites) {
			if(url.indexOf(this.sites[key], 0) != -1) {
				var site = this.sites[key];
				break;
			}
		}

		if (!site) {
			$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Сайт не поддерживается</strong></div>");
			return;
		}

		$.ajax({
			url: this.url_list['get_manga_info'],
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

					var tr = $(html).find('div.chapters-link table.table tr');
					if (tr.length > 1) {
						var a_array = [];

						for(var i=(tr.length-1); i>0; i--) {
							var href = $(tr).eq(i).find('td').eq(0).find('a').eq(0).attr('href');
							var text = $(tr).eq(i).find('td').eq(0).find('a').eq(0).text();
							var a = "<div class='chapter_div'>";
							a += "<table class='table'>";
								a += "<tr>";
									a += "<td><input type='checkbox' class='checkbox_download' checked></td>";
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
	},

	/**
	 * Сохранение информации о манге
	 */
	saveMangaInfo: function() {
		$('.main div.alert').remove();

		var folder = $('#folder').val();
		var description = $('#manga td.manga div.description').text().trim();
		var img = $('#manga td.manga').find('img.image').eq(0).attr('src');

		$.ajax({
			url: this.url_list['save_manga_description'],
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
	},

	/**
	 * Поиск манги на локальном компьютере
	 */
	getMangaFromFolder: function() {
		$('.main div.alert').remove();
		$("#manga span.folder").empty();

		var folder = $("#folder").val();
		var html = '';

		$.ajax({
			url: this.url_list['get_manga_folder'],
			data: {"folder": folder},
			type: "POST",
			dataType: 'json',
			success: function(json) {
				if (json && json['success'] && json['success'] === 'true') {

					if (json['folder'].length > 0) {
						var error = [];
						for(var i=0; i<json['folder'].length; i++) {
							var a = $('#manga .chapter_link[data-href $= "'+json['folder'][i]['name']+'"]');

							if (a.length == 1) {
								var count_image = $(a).parents('tr').eq(0).find('span.count').text();
								$(a).parents('tr').eq(0).find('span.folder').text(json['folder'][i]['name']+' ('+json['folder'][i]['count']+')');
							} else {
								error.push(json['folder'][i]['name']+' ('+json['folder'][i]['count']+')');
							}

							if (count_image && count_image != ('('+json['folder'][i]['count']+')')) {
								error.push(json['folder'][i]['name']+' ('+json['folder'][i]['count']+')');
							}

						}

						if (error.length > 0) {
							error = error.join('<br><br>');
							$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>"+error+"</strong></div>");
						}

					} else {
						$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Данных нет</strong></div>");
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
	},

	getImageListFromChapter: function(href) {
		$('.main div.alert').remove();
		var self = this;

		$.ajax({
			url: this.url_list['get_image_list'],
			data: {"url": href},
			type: "POST",
			dataType: 'json',
			success: function(json) {
				if (json && json['success'] && json['success'] == 'true') {
					var list = json['list'];
					self.image_list[url] = list;

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
	},

	/**
	 * Скачивание картинок главы
	 * @param string url
	 */
	downloadImageListFromChapter: function(url) {
		$('.main div.alert').remove();

		if (!this.image_list[url]) {
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
			url: this.url_list['get_download_list'],
			data: {"list": this.image_list[url], 'volume': data[4], 'chapter': data[5], 'folder': folder},
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
	},

	/**
	 * Рекурсивное получение ссылок на главы
	 * @param int index
	 * @param array list
	 */
	recursionDownloadChapterLink: function(index, list) {
		var self = this;

		$('.main div.alert').remove();

		var url = $('#manga td.chapter a.download').eq(list[index]).attr('data-href');
		var next_index = index+1;

		if (this.image_list[url]) {
			var len = 0;
			for(var key in this.image_list[url]) {
				len++;
			}
			$('#manga td.chapter').find('a[data-href="'+url+'"]').parents('div').eq(0).find('span.count').text('('+len+')');

			if (list[next_index]) {
				self.recursionDownloadChapterLink(next_index, list);
				return;
			} else {
				$('.main h2').after("<div class='alert alert-dismissable alert-success'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Все данные загружены</strong></div>");
			}
		}

		$.ajax({
			url: this.url_list['get_image_list'],
			data: {"url": url},
			type: "POST",
			dataType: 'json',
			success: function(json) {
				if (json && json['success'] && json['success'] == 'true') {
					var list_link = json['list'];
					self.image_list[url] = list_link;

					var len = 0;
					for(var key in list_link) {
						len++;
					}

					$('#manga td.chapter').find('a[data-href="'+url+'"]').parents('div').eq(0).find('span.count').text('('+len+')');

					if (list[next_index]) {
						// если данные в кеше, то следующий запрос делаем сразу
						if (json['cache'] && (json['cache'] == 'true' || json['cache'] == true)) {
							self.recursionDownloadChapterLink(next_index, list);
						} else {
							setTimeout(function() {
								self.recursionDownloadChapterLink(next_index, list);
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
	},

	/**
	 * Рекурсивное скачивание изображений по главам
	 * @param int index
	 * @param array list
	 */
	recursionDownloadImageList: function(index, list) {
		var self = this;

		$('.main div.alert').remove();
		var next_index = index+1;
		var url = $('#manga td.chapter a.download').eq(list[index]).attr('data-href');
		if (!this.image_list[url]) {
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
			url: this.url_list['get_download_list'],
			data: {"list": this.image_list[url], 'volume': data[4], 'chapter': data[5], 'folder': folder},
			timeout: 300000,
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
						self.recursionDownloadImageList(next_index, list);
					}, 1000);
				}

			},
			error: function() {
				$('.main h2').after("<div class='alert alert-dismissable alert-danger'><button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button><strong>Не удалось загрузить данные</strong></div>");
			},
		});
	},

	/**
	 * Получение поддерживаемых сайтов
	 * @return array
	 */
	getAccessSiteList: function() {
		return this.sites;
	},

	clear: function() {
		$('#manga').find('td.manga, td.chapter').empty();
	},

}
