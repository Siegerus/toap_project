var AvitoModule = (function() {
	
	return { 
		changeDisabled: false,
		customOptions: [],
		productFields: null,
		props: null,
		customOptionsDataFetched: false,
		init: function() {

			//выбор локации
			$("#region").combobox();

			$('.integration-container').on('change', '#region', function() {
				if (AvitoModule.changeDisabled) {return false;}
				var region = $('#region').val();
				if (region > -1) {
					admin.ajaxRequest({
						mguniqueurl: "action/getCitysAvito",
						region: region,
					},
					function (response) {
						AvitoModule.comboboxDestroy("#city");
						$("#city").html(response.data);
						$("#city").combobox();
					});
				}
				else{
					AvitoModule.comboboxDestroy("#city");
					$("#city").html('<option value="-5">Для выбора города выберите регион</option>');
					$("#city").val(-5);
				}
				AvitoModule.comboboxDestroy("#subway");
				AvitoModule.comboboxDestroy("#district");
				$("#subway").html('<option value="-5">Метро не указано</option>');
				$("#subway").val(-5);
				$("#subway").closest('.row').hide();
				$("#district").html('<option value="-5">Район не указан</option>');
				$("#district").val(-5);
				$("#district").closest('.row').hide();
			});

			$('.integration-container').on('change', '#city', function() {
				if (AvitoModule.changeDisabled) {return false;}
				var city = $('#city').val();
				if (city > -1) {
					admin.ajaxRequest({
						mguniqueurl: "action/getSubwaysAvito",
						city: city,
					},
					function (response) {
						AvitoModule.comboboxDestroy("#subway");
						AvitoModule.comboboxDestroy("#district");
						if (response.data.subways != '') {
							$("#subway").html(response.data.subways);
							$("#subway").combobox();
							$("#subway").closest('.row').show();
						}
						else{
							$("#subway").html('<option value="-5">Метро не указано</option>');
							$("#subway").val(-5);
							$("#subway").closest('.row').hide();
						}
						if (response.data.districts != '') {
							$("#district").html(response.data.districts);
							$("#district").combobox();
							$("#district").closest('.row').show();
						}
						else{
							$("#district").html('<option value="-5">Район не указан</option>');
							$("#district").val(-5);
							$("#district").closest('.row').hide();
						}
						$("#exact").closest('.row').show();
					});
				}
				else{
					AvitoModule.comboboxDestroy("#subway");
					AvitoModule.comboboxDestroy("#district");
					$("#subway").closest('.row').hide();
					$("#district").closest('.row').hide();
				}
			});
			// /выбор локации

			if ($('.integration-container .template-tabs-menu .template-tabs').length > 1) {
				$('.integration-container .template-tabs-menu .template-tabs').show();
			}
			else{
				$('.integration-container .template-tabs-menu .template-tabs').hide();
			}

			 // Добавляет вкладку
			$('.integration-container').on('click', '.newNameSave', function() {

				//удаление хлама
				var nname = $(".integration-container input[name=newName]").val();
				//nname = nname.replace( /\s/g, "");
				nname = nname.toLowerCase();
				nname = nname.replace(/[^0-9a-z]/g, '');

				if (nname == '') {
					admin.indication('error', lang.UPLOAD_NAME);
				}
				else{
					admin.ajaxRequest({
						mguniqueurl: "action/newTabAvito",
						name: nname,
					},
					function (response) {
						if (response.data == false && nname != 0) {
							admin.indication('error', lang.NAME_ALREADY_EXISTS);
						} else {
							$('.integration-container .template-tabs-menu .template-tabs').show();
							$(".integration-container input[name=newName]").val('');
							admin.indication('success', lang.NEW_UPLOAD_CREATED);
							$('<li class="template-tabs button primary clickMe" name="'+response.data+'"><a role="button" href="javascript:void(0);" ><span>'+response.data+'</span></a></li>').insertAfter(".creator");
							$('<li style="display:inline-block;width:4px;"></li>').insertAfter(".creator");
							$('.clickMe').click().removeClass('clickMe');
						}
					});
				}
			});

			//преключение табов
			$('.integration-container').on('click', '.template-tabs', function() {

				$(this).parent().find('li').removeClass('is-active');
				$(this).addClass('is-active');
				var nname = $(this).attr('name');
				AvitoModule.resetTable();
				AvitoModule.updateLink(nname);
				if (nname.length > 0) {
					$('.newName').hide();
					$('.editOld').show();
					$('.editOldSave').attr('name', nname);
					$('.editOldDelete').attr('name', nname);

					admin.ajaxRequest({
						mguniqueurl: "action/getTabAvito",
						name: nname,  
					},

					function(response) {
						if ($.map(response.data, function() { return 1; }).length > 1) {
							$('.bottomBorder').show();
							$('#downloadLink').show();
						}
						else{
							$('#downloadLink').hide();
							$('.bottomBorder').hide();
						}
						// console.log(response);

						AvitoModule.changeDisabled = true;

						$("#region").combobox('set', '-5');
						if (response.data.region > 0) {
							$("#region").combobox('set', response.data.region);
						}

						AvitoModule.comboboxDestroy("#city");
						AvitoModule.comboboxDestroy("#subway");
						AvitoModule.comboboxDestroy("#district");
						
						$("#city").html(response.data.cityOptions);
						$("#city").val(-5);
						
						if (response.data.city > 0) {
							$("#city").combobox();
							$("#city").combobox('set', response.data.city);
						}

						if (response.data.subwayOptions != '' && response.data.subway > 0) {
							$("#subway").html(response.data.subwayOptions);
							$("#subway").combobox();
							$("#subway").combobox('set', response.data.subway);
							$("#subway").closest('.row').show();
						}
						else{
							$("#subway").html('<option value="-5">Метро не указано</option>');
							$("#subway").val(-5);
							$("#subway").closest('.row').hide();
						}

						if (response.data.districtOptions != '' && response.data.district > 0) {
							$("#district").html(response.data.districtOptions);
							$("#district").combobox();
							$("#district").combobox('set', response.data.district);
							$("#district").closest('.row').show();
						}
						else{
							$("#district").html('<option value="-5">Район не указан</option>');
							$("#district").val(-5);
							$("#district").closest('.row').hide();
						}

						if (response.data.exact) {
							$("#exact").html(response.data.exact);
							$('#exact').closest('.row').show();
						}

						AvitoModule.updateText();
						$(".editOld input[name=manager]").val(response.data.manager);
						$(".editOld input[name=exact]").val(response.data.exact);
						$(".editOld input[name=phone]").val(response.data.phone);
						$(".editOld input[name=avitoMargin]").val(response.data.avitoMargin);
						AvitoModule.drawCheckbox(response.data.useNull, 'useNull');
						AvitoModule.drawCheckbox(response.data.useCdata, 'useCdata');
						AvitoModule.drawCheckbox(response.data.inactiveToo, 'inactiveToo');
						AvitoModule.drawCheckbox(response.data.shortDescription, 'shortDescription');
						AvitoModule.drawRelatedProduct(response.data.ignoreProducts);
						AvitoModule.changeDisabled = false;
					});
				}
				else{
					$('.newName').show();
					$('.editOld').hide();
				}
			});

			//сохранение таба
			$('.integration-container').on('click', '.editOldSave', function() {

				var nname = $(this).attr('name');

				var region = $('#region').val();
				$('#region').removeClass('error-input');
				$('#region').parent().find('.ui-autocomplete-input').removeClass('error-input');
				if (region < 0) {
					$('#region').addClass('error-input');
					$('#region').parent().find('.ui-autocomplete-input').addClass('error-input');
					return false;
				}

				var city = $('#city').val();
				$('#city').removeClass('error-input');
				$('#city').parent().find('.ui-autocomplete-input').removeClass('error-input');
				if (city < 0) {
					$('#city').addClass('error-input');
					$('#city').parent().find('.ui-autocomplete-input').addClass('error-input');
					return false;
				}

				var exact = $('#exact').val();
				$('#exact').removeClass('error-input');
				$('#exact').parent().find('.ui-autocomplete-input').removeClass('error-input');
				if (city < 0) {
					$('#exact').addClass('error-input');
					$('#exact').parent().find('.ui-autocomplete-input').addClass('error-input');
					return false;
				}

				admin.ajaxRequest({
					mguniqueurl: "action/saveTabAvito",
					name: nname,  
					data: {
						region : region,
						city : city,
						exact : exact,
						subway : $('#subway').val(),
						district : $('#district').val(),
						manager : $(".editOld input[name=manager]").val(),
						phone : $(".editOld input[name=phone]").val(),
						useNull : $(".editOld input[name=useNull]").prop('checked'),
						useCdata : $(".editOld input[name=useCdata]").prop('checked'),
						inactiveToo : $(".editOld input[name=inactiveToo]").prop('checked'),
						ignoreProducts : AvitoModule.getRelatedProducts(),
						shortDescription: $(".editOld input[name=shortDescription]").prop('checked'),
						avitoMargin : $(".editOld input[name=avitoMargin]").val(),
					},
				},

				function(response) {
					$('.bottomBorder').show();
					$('#downloadLink').show();
					admin.indication(response.status, lang.SAVED);
				});
				
			});

			//удаление таба
			$('.integration-container').on('click', '.editOldDelete', function() {

				var nname = $(this).attr('name');

				admin.ajaxRequest({
					mguniqueurl: "action/deleteTabAvito",
					name: nname,  
				},

				function(response) {
					$('.tabs-list').children('li[name="'+nname+'"]').remove();
					$('.tabs-list').children('li[name=""]').click();
					admin.indication(response.status, lang.DELETED);
				});
				
			});

			//Заполнение базы
			$('.integration-container').on('click', '.updateDB', function() {

				admin.ajaxRequest({
					mguniqueurl: "action/updateDBAvito", 
				},

				function(response) {
					admin.indication(response.status, lang.FILLED);
					window.location.reload(true);
				});
				
			});

			//изменение типа тегов
			$('.integration-container').on('click', '.customTagsContainer .changeCustomTagType', function () {
				if ($(this).attr('tagType') == 'prop') {
					$(this).attr('tagType', 'text');
					$(this).parent().parent().find('.customProp').hide();
					$(this).parent().parent().find('.customTagText').show();
				}
				else if ($(this).attr('tagType') == 'text') {
					$(this).attr('tagType', 'prop');
					$(this).parent().parent().find('.customProp').show();
					$(this).parent().parent().find('.customTagText').hide();
				}
			});

			// Разворачивание подпунктов по клику в интеграции
	      $('.integration-container').on('click', '.integraion-category .show_sub_menu', function() {
	        var object = $(this).parents('tr');
	        var id = $(this).parents('tr').data('id');
	        var level = $(this).parents('tr').data('level');
	        var group = 'group-'+$(this).parents('tr').data('id');
	        level++;

	        thisSortNumber = 0;
	        isFindeSorte = false;
	        $('.integraion-category .main-table tbody tr').each(function() {
	          if($(this).data('id') == id) {
	            isFindeSorte = true;
	          }
	          if(!isFindeSorte) {
	            thisSortNumber++;
	          }
	        });

	        if ($(this).hasClass('opened')) {

	          category.group = $(this).parents('tr').data('group');

	          var trCount = $('.integraion-category .main-table tbody tr').length;

	          var startDel = false;
	          $('.integraion-category .main-table tbody tr').each(function() {
	            if($(this).data('level') >= level) {
	              if($(this).data('group') == group) {
	                startDel = true;
	              }
	            }
	            if(startDel) {
	              if($(this).data('level') >= level) {
	                $(this).detach();
	              } else {
	                startDel = false;
	              }
	            }
	          });

	          $(this).removeClass('opened');
	        } else {
	          object.after('\
	            <tr id="loader-'+id+'">\
	              <td style="padding-left:40px;"><img src="'+admin.SITE+'/mg-admin/design/images/loader-small.gif"></td>\
	              <td></td>\
	            </tr>');
	          admin.ajaxRequest({
	            mguniqueurl: "action/showSubCategorySimple",
	            id: id,
	            level: level
	          },
	          function(response) {      
	            $('#loader-'+id).detach();
	            object.after(response.data);
	            category.hidePageRows(level+1);
	            AvitoModule.updateText();
	          });

	          $(this).addClass('opened');
	          
	        }
	      });
	      
			document.cookie = 'openedCategoryAdmin' + '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';

			//игнор товаров
			// показывает сроку поиска для связанных товаров
			$('.integration-container').on('click', '.add-related-product', function() {
				$('.select-product-block').show();
			});

			// Удаляет связанный товар из списка связанных
			$('.integration-container').on('click', '.add-related-product-block .remove-added-product', function() {
				$(this).parents('.product-unit').remove();
				AvitoModule.widthRelatedUpdate();
				AvitoModule.msgRelated();
			});

			// Закрывает выпадающий блок выбора связанных товаров
			$('.integration-container').on('click', '.add-related-product-block .cancel-add-related', function() {
				$('.select-product-block').hide();
			});

			// Поиск товара при создании связанного товара.
			// Обработка ввода поисковой фразы в поле поиска.
			$('.integration-container').on('keyup', '.search-block input[name=searchcat]', function() {
				admin.searchProduct($(this).val(),'.integration-container .search-block .fastResult', -1, 'nope', false);
			});

			// Подстановка товара из примера в строку поиска связанного товара.
			$('.integration-container').on('click', '.search-block  .example-find', function() {
				$('.section-catalog .search-block input[name=searchcat]').val($(this).text());
				admin.searchProduct($(this).text(),'.integration-container .search-block .fastResult', -1, 'nope', false);
			});

			// Клик по найденым товарам поиска в форме добавления связанного товара.
			$('.integration-container').on('click', '.fast-result-list a', function() {
				AvitoModule.addrelatedProduct($(this).data('element-index'));
			});
			//игнор товаров/////////////

			//модалка
			$('.integration-container').on('click', '.integraion-category .upload-cat-text', function() {
				admin.openModal('#add-avito-category-modal');
				AvitoModule.fillModal($(this).attr('upload-cat-name'), $(this).attr('data-cat-id'));
				$('.integration-container #add-avito-category-modal .save-button').attr('shopId', $(this).attr('data-cat-id'));
			});

			$('.integration-container').on('change', '#add-avito-category-modal .reveal-body .customCatSelect', function() {
				var tmp = $(this).val();
				if (tmp != -5) {
					AvitoModule.fillModal(tmp);
				}
				else{
					var last = AvitoModule.findLast();
					AvitoModule.fillModal(last);
				}
			});

			$('.integration-container').on('click', '#add-avito-category-modal .save-button', function() {

				var count = $('#add-avito-category-modal .customCatSelect').length;
				$('#add-avito-category-modal .reveal-body .customCatSelect').removeClass('error-input');
				for (var j=0; j<count; j++) {
					tmp = $('#add-avito-category-modal .reveal-body .customCatSelect:eq('+j+')').val();
					if (tmp == -5) {
						$('#add-avito-category-modal .reveal-body .customCatSelect:eq('+j+')').addClass('error-input');
						return false;
					}
				}

				count = $('#add-avito-category-modal .additionalCatSelect').length;
				var additional = [];
				if (count) {
					for (var i = 0; i < count; i++) {
						additional.push({'paramName':$('#add-avito-category-modal .additionalCatSelect:eq('+i+')').attr('paramName'), 'val':$('#add-avito-category-modal .additionalCatSelect:eq('+i+')').val()});
					}
				}

				shopCatId = $(this).attr('shopId');
				var googleCatId = AvitoModule.findLast();
				nname = $('.template-tabs-menu').find('.is-active').attr('name');

				admin.ajaxRequest({
					mguniqueurl: "action/saveCatAvito",
					shopId: shopCatId,
					googleId: googleCatId,
					name: nname,
					additional: additional,
					customOptions: AvitoModule.customOptions,
				},

				function(response) {
					AvitoModule.updateText();
					admin.indication(response.status, lang.SAVED);
					admin.closeModal('#add-avito-category-modal');
				});
			});
			//модалка///////////////////
			//таблица с категориями
			$('.integration-container').on('click', '.cat-apply-follow', function() {
				var shopCatId = $(this).parent().parent().find('.upload-cat-text').attr('data-cat-id');
				var googleCatId = $(this).parent().parent().find('.upload-cat-text').attr('upload-cat-name');
				nname = $('.template-tabs-menu').find('.is-active').attr('name');

				admin.ajaxRequest({
					mguniqueurl: "action/updateCatsRecursAvito",
					shopId: shopCatId,
					googleId: googleCatId,
					name: nname
				},

				function(response) {
					AvitoModule.updateText();
					admin.indication(response.status, lang.SAVED);
				});
			});

			$('.integration-container').on('click', '.cat-cansel', function() {
				var shopCatId = $(this).parent().parent().find('.upload-cat-text').attr('data-cat-id');
				var googleCatId = 0;
				nname = $('.template-tabs-menu').find('.is-active').attr('name');

				admin.ajaxRequest({
					mguniqueurl: "action/saveCatAvito",
					shopId: shopCatId,
					googleId: googleCatId,
					name: nname
				},

				function(response) {
					AvitoModule.updateText();
					admin.indication(response.status, lang.SAVED);
				});
			});
			//таблица с категориями//////

			// жесткий костыль для выгрузки автомобильных аксессуаров, динамически подставляет селекты для выгрузки
			$('.integration-container').on('change', 'select.additionalCatSelect[paramname="TypeId"]', function() {
				const catsSelects = $('.integration-container select.customCatSelect');
				if (catsSelects.length !== 2) {
					return;
				}
				const catTitle = $(catsSelects[0]).children('option:selected').text();
				const subCatTitle = $(catsSelects[1]).children('option:selected').text();
				if (catTitle != 'Транспорт' || subCatTitle != 'Запчасти и аксессуары') {
					return;
				}

				const typeId = $(this).children('option:selected').text();

				if (typeId == 'Аксессуары') {
					// тут нужно проверить а нет ли кастомных селектов уже
					if ($('select.additionalCalSelect[paramname="ProductType"]').length) {
						return;
					}
					const productTypes = [
						'Щётки стеклоочистителя',
						'Защита и декор',
						'Для салона',
						'Для колёс',
						'Для мото- и водного транспорта',
						'Отопительное оборудование',
						'Набор автомобилиста',
						'Уход',
					];
					let productTypeSelect = '<select class="additionalCatSelect" paramname="ProductType">';
					for (productTypeIndex in productTypes) {
						productType = productTypes[productTypeIndex];
						let selected = '';
						if (productTypeIndex === 0) {
							selected = ' selected';
						}
						productTypeSelect += `<option value="${productType}"${selected}>${productType}</option>`;
					}
					productTypeSelect += '</select>';
					$(this).after(productTypeSelect);
				} else {
					// тут нужно удалить кастомные селекты для аксов
				}
			});
			$('.integration-container').on('change', 'select.additionalCatSelect[paramname="ProductType"]', function() {
				const catsSelects = $('.integration-container select.customCatSelect');
				if (catsSelects.length !== 2) {
					return;
				}
				const catTitle = $(catsSelects[0]).children('option:selected').text();
				const subCatTitle = $(catsSelects[1]).children('option:selected').text();
				if (catTitle != 'Транспорт' || subCatTitle != 'Запчасти и аксессуары') {
					return;
				}
				$('select.additionalCatSelect[paramname="AccessoryType"]').remove();
				const productType = $(this).children('option:selected').text();
				const accessoryTypes = {
					'Уход': [
						'Чехлы-тенты',
						'Мойки высокого давления',
						'Автопылесосы',
						'Щётки и скребки',
					],
					'Для колёс': [
						'Болты и крепления',
						'Датчики давления',
						'Центровочные кольца и проставки для дисков',
						'Цепи противоскольжения',
					],
					'Набор автомобилиста': [
						'Троссы и лебёдки',
						'Зарядные устройства',
						'Компрессоры, насосы и манометры',
						'Канистры и воронки',
						'Готовый комплект автомобилиста',
						'Знак аварийной остановки',
						'Домкраты',
						'Аптечки',
						'Огнетушители',
						'Алкотестеры',
						'Светоотражающие жилеты',
					],
					'Защита и декор': [
						'Для фар, порогов и бамперов',
						'Наклейки, шильдики и значки',
						'Дефлекторы',
						'Для картера и КПП',
						'Брызговики и подкрылки',
						'Рамки номерного знака',
						'Силовые бамперы и пороги',
						'Утеплители',
						'Дополнительный свет',
						'Шноркели',
					],
					'Для салона': [
						'Коврики',
						'Чехлы и накидки',
						'Ключи и брелоки',
						'Шторки',
						'Сумки и органайзеры',
						'Оплётка на руль',
						'Подлокотники',
						'Держатели для телефона',
						'Ручки КПП',
						'Автохолодильники',
						'Другое',
					],
				};
				if (!accessoryTypes[productType]) {
					return;
				}
				let accessoryTypeSelect = '<select class="additionalCatSelect" paramname="AccessoryType">';
				for (accessoryTypeIndex in accessoryTypes[productType]) {
					accessoryType = accessoryTypes[productType][accessoryTypeIndex];
					let selected = '';
					if (accessoryTypeIndex === 0) {
						selected = ' selected';
					}
					accessoryTypeSelect += `<option value="${accessoryType}"${selected}>${accessoryType}</option>`;
				}
				accessoryTypeSelect += '</select>';
				$(this).after(accessoryTypeSelect);
			});

			$('.integration-container').on('change', 'select.additionalCatSelect[paramname="ToolType"]', function() {
				if($($('.integration-container select.customCatSelect')[2]).children('option:selected').text() === 'Инструменты') {
					$('select.additionalCatSelect[paramname="ToolSubType"]').prop( "disabled", false );
					const instrumentType = $(this).val();
					const instrumentTypesWithSubTypes = {
						'Электроинструменты': [
							'Болгарки (УШМ)',
							'Дрели и шуруповерты',
							'Миксеры строительные',
							'Отбойные молотки',
							'Перфораторы',
							'Пилы электрические',
							'Пистолеты монтажные',
							'Плиткорезы',
							'Пылесосы строительные',
							'Фены строительные',
							'Фрезеры',
							'Шлифовальные машины',
							'Электролобзики',
							'Электрорубанки',
							'Другое',
						],
						'Ручные инструменты': [
							'Домкраты',
							'Ключи',
							'Малярные инструменты',
							'Наборы инструментов',
							'Ножницы',
							'Отвёртки',
							'Пилы и лобзики',
							'Пистолеты для пены и герметика',
							'Плиткорезы',
							'Столярные инструменты',
							'Тиски и струбцины',
							'Ударно-рычажные инструменты',
							'Другое',
						],
						'Измерительные инструменты': [
							'Лазерные рулетки и дальномеры',
							'Лазерные уровни и нивелиры',
							'Пирометры и прочие детекторы',
							'Разметочные инструменты',
							'Рулетки',
							'Ручные измерительные инструменты',
							'Строительные уровни',
							'Штативы, рейки, держатели',
							'Электроизмерительные приборы и тестеры',
							'Другое',
						],
						'Газовое и сварочное оборудование': [
							'Аксессуары и комплектующие для сварки',
							'Газовые горелки и паяльные лампы',
							'Маски и перчатки для сварки',
							'Паяльники и аксессуары',
							'Сварочные аппараты',
							'Электроды, проволока, прутки',
						],
						'Расходные материалы и оснастка': [
							'Алмазные диски',
							'Буры, долота, пики',
							'Зарядные устройства и аккумуляторы',
							'Коронки',
							'Отрезные круги и пильные диски',
							'Патроны и переходники',
							'Свёрла',
							'Шлифовальные круги и насадки',
							'Другое',
						],
						'Другие инструменты': [
							'Аппараты',
							'Верстаки и оборудование для мастерской',
							'Компрессоры',
							'Лестницы, стремянки, вышки-туры',
							'Насосы и комплектующие',
							'Такелаж',
						],
						'Силовая, строительная техника и комплектующие': [
							'Бетономешалки',
							'Вибраторы',
							'Виброплиты',
							'Станки',
							'Установки для бурения',
						],
						'Электрика': [
							'Генераторы',
							'Двигатели',
							'Кабели и провода',
							'Стабилизаторы напряжения',
							'Трансформаторы',
							'Другое',
						],
						'Садовая техника': [
							'Газонокосилки и триммеры',
							'Канистры',
							'Лопаты',
							'Тачки и тележки',
						],
						'Спецодежда и средства защиты': [
							'Каски строительные',
							'Маски, очки',
							'Наушники и беруши',
							'Неодимовые магниты',
							'Огнетушители',
							'Перчатки, рукавицы',
							'Пояса, ремни, сумки',
							'Респираторы',
							'Спецодежда, обувь и наколенники',
							'Другое',
						],
					};
					if (!instrumentTypesWithSubTypes[instrumentType]) {
						$('select.additionalCatSelect[paramname="ToolSubType"]').prop( "disabled", true );
						return;
					}
					const instrumentSubTypes = instrumentTypesWithSubTypes[instrumentType];
					let newSubTypesOptions = '';
					for (const subType of instrumentSubTypes) {
						newSubTypesOptions += '<option value="'+subType+'">'+subType+'</option>';
					}
					$('select.additionalCatSelect[paramname="ToolSubType"]').html(newSubTypesOptions);
				}
			});

			$('.integration-container').on('click', '.customOptionAddButton', function() {
				const optionNameInput = $('input.customOptionName');
				const optionType = $('select.customOptionTypeSelect').val();
				const optionName = optionNameInput.val();

				for (customOption of AvitoModule.customOptions) {
					if (customOption.name === optionName) {
						admin.indication('error', 'Поле с таким именем уже существует!');
						return false;
					}
				}

				if (!optionType || !optionName) {
					return false;
				}
				AvitoModule.customOptions.push({
					type: optionType,
					name: optionName,
					value: '',
				});
				optionNameInput.val('');
				AvitoModule.regenCustomOptions();
			});

			$('.integration-container').on('click', '.customOptionDeleteButton', function() {
				const customOptionElement = $(this).parent('.customOption');
				const optionName = customOptionElement.data('name');
				if (!optionName) {
					return false;
				}
				for (customOptionIndex in AvitoModule.customOptions) {
					const customOption = AvitoModule.customOptions[customOptionIndex];
					if (customOption.name === optionName) {
						AvitoModule.customOptions.splice(customOptionIndex, 1);
						break;
					}
				}
				customOptionElement.remove();
			});

			$('.integration-container').on('change', '.customOption .customConstOption,.customPropOption,.customProductFieldOption', function() {
				//debugger;
				const customOptionValue = $(this).val();
				const customOptionName = $(this).parent('.customOption').data('name');
				for(customOptionIndex in AvitoModule.customOptions) {
					const customOption = AvitoModule.customOptions[customOptionIndex];
					if (customOption.name === customOptionName) {
						AvitoModule.customOptions[customOptionIndex].value = customOptionValue;
						return true;
					}
				}
				return false;
			});
		},

		regenCustomOptions: function() {
			if (!AvitoModule.productFields || !AvitoModule.props) {
				AvitoModule.fetchProductFieldsAndProps();
				return;
			}
			$('.integration-container .customOptionsContainer').remove();
			let customOptionsHtml = '<div class="customOptionsContainer" style="width: min-content; margin: 0 auto; display: flex; flex-direction: column; align-items: flex-end;">';
			for(customOption of AvitoModule.customOptions) {
				//const customOption = AvitoModule.customOptions[customOptionIndex];
				customOptionsHtml += `<div class="customOption" style="display: flex; align-items: center;" data-name="${customOption.name}">
					<p class="customOptionTitle" style="margin-right: 10px;">${customOption.name}:</p>`;
				switch(customOption.type) {
					case 'const':
						customOptionsHtml += `<input type="text" class="customConstOption" style="margin-right: 10px; width: 400px;" value="${customOption.value}" />`;
						break;
					case 'prop':
						let propsOptions = '';
						if (!customOption.value) {
							customOption.value = AvitoModule.props[0].value;
						}
						for (prop of AvitoModule.props) {
							let selected = '';
							if (prop.value === customOption.value) {
								selected = 'selected="selected"';
							}
							propsOptions += `<option value="${prop.value}" ${selected}>${prop.title}</option>`;
						}
						customOptionsHtml += `<select class="customPropOption" style="margin-right: 10px; width: 400px;" >
								${propsOptions}
							</select>`;
						break;
					case 'productField':
						let productFieldsOptions = '';
						if (!customOption.value) {
							customOption.value = AvitoModule.productFields[0].value;
						}
						for (productField of AvitoModule.productFields) {
							let selected = '';
							if (productField.value === customOption.value) {
								selected = 'selected="selected"';
							}
							productFieldsOptions += `<option value="${productField.value}" ${selected}>${productField.title}</option>`;
						}
						customOptionsHtml += `<select class="customProductFieldOption" style="margin-right: 10px; width: 400px;" >
								${productFieldsOptions}
							</select>`;
						break;
				}
				customOptionsHtml += `<a class="customOptionDeleteButton" href="javascript:void(0);" style="color: red; margin-bottom: 10px;"><i class="fa fa-times"></i></a></div>`;
			}
			customOptionsHtml += '</div>';
			$('#add-avito-category-modal .customOptionCreator').before(customOptionsHtml);
		},
		fetchProductFieldsAndProps: function() {
			if (AvitoModule.customOptionsDataFetched) {
				return false;
			}
			admin.ajaxRequest(
				{
					mguniqueurl: 'action/getProductFieldsAndPropsAvito',
				},
				function(response) {
					admin.indication(response.status, response.msg);
					if (response.status === 'success') {
						AvitoModule.productFields = response.data.productFields;
						AvitoModule.props = response.data.props;
						AvitoModule.customOptionsDataFetched = true;
						AvitoModule.regenCustomOptions();
					}
				}
			);
		},

		comboboxDestroy: function(obj) {
			if ($(obj).parent().find('.ui-autocomplete-input').length) {
				$(obj).combobox('destroy', '');
			}
			else{
				return false;
			}
		},

		fillModal: function(catId, shopCatId) {
			AvitoModule.customOptions = [];
			if (typeof shopCatId === "undefined" || shopCatId === null) { 
				shopCatId = -5;
			}
			admin.ajaxRequest({
				mguniqueurl: "action/buildSelectsAvito",
				id: catId,
				shopCatId: shopCatId,
				uploadName: $('.integration-container .editOldSave').attr('name')
			},

			function(response) {
				// console.log(response);
				$('#add-avito-category-modal .reveal-body').html('');
				$('#add-avito-category-modal .reveal-body').html(response.data.html);
				$('#add-avito-category-modal .reveal-body .customCatSelect').val(-5);

				for (var j=0; j<response.data.choices.length; j++) {
					if (response.data.choices[j]>0) {
						$('#add-avito-category-modal .reveal-body .customCatSelect:eq('+j+')').val(response.data.choices[j]);
					}
				}

				const customOptionCreatorHtml = `
					<p>Свои параметры:</p>
					<div class="customOptionCreator" style="display: flex; align-items: center;">
						<select class="customOptionTypeSelect" style="margin: 0 10px 0 0;">
							<option value="const">Значение по умолчанию</option>
							<option value="prop">Характеристика товара</option>
							<option value="productField">Поле товара</option>
						</select>
						<input type="text" class="customOptionName" placeholder="Название тега, например, GoodsType" style="margin: 0 10px 0 0;" />
						<a role="button" href="javascript:void(0);" class="customOptionAddButton" style="margin: 0;">Добавить</a>
					</div>
				`;

				$('#add-avito-category-modal .reveal-body').append(customOptionCreatorHtml);

				if (response.data.customOptions) {
					AvitoModule.customOptions = response.data.customOptions;
				}
				AvitoModule.regenCustomOptions();
			});
		},

		findLast: function(catId) {
			var count = $('.customCatSelect').length;
			var res = 0;
			var tmp = '';

			for (var j=0; j<count; j++) {
				tmp = $('#add-avito-category-modal .reveal-body .customCatSelect:eq('+j+')').val();
				if (tmp != -5) {
					res = tmp;
				}
				else{
					return res;
				}
			}
			return res;
		},

		resetTable: function() {
			$('.integration-container .category-tree').find('.sticker-menu').addClass('alert');
			$('.integration-container .category-tree').find('.sticker-menu').removeClass('success');
			$('.integration-container .category-tree').find('.upload-cat-text').attr('upload-cat-name', 0);
			$('.integration-container .category-tree').find('.upload-cat-text').text('Привязать категорию');
		},

		updateText: function() {

			nname = $('.template-tabs-menu').find('.is-active').attr('name');

			admin.ajaxRequest({
				mguniqueurl: "action/getCatsAvito",
				name: nname
			},

			function(response) {
				$.each( response.data, function( index, value ){
					if ($('.integration-container .category-tree [data-id='+index+']').length >0) {

						if (value > 0) {
							$('.integration-container .category-tree [data-id='+index+']').find('.sticker-menu').removeClass('alert');
							$('.integration-container .category-tree [data-id='+index+']').find('.sticker-menu').addClass('success');
							var objj = $('.integration-container .category-tree [data-id='+index+']').find('.upload-cat-text');
							objj.attr('upload-cat-name', value);

							admin.ajaxRequest({
								mguniqueurl: "action/getCatNameAvito",
								id: value,
							},

							function(response) {
								objj.text(response.data);
							});
						}

						else{
							$('.integration-container .category-tree [data-id='+index+']').find('.sticker-menu').addClass('alert');
							$('.integration-container .category-tree [data-id='+index+']').find('.sticker-menu').removeClass('success');
							$('.integration-container .category-tree [data-id='+index+']').find('.upload-cat-text').attr('upload-cat-name', value);
							$('.integration-container .category-tree [data-id='+index+']').find('.upload-cat-text').text('Привязать категорию');
						}
					}
				});

				$('.integration-container .category-tree tr').each(function() {
					if ($(this).find('.show_sub_menu').length > 0) {
						$(this).find('.cat-apply-follow').show();
					}
					else{
						$(this).find('.cat-apply-follow').hide();
					}
				});
			});
			
		},

		widthRelatedUpdate: function() {
			var prodWidth = $('.product-unit').length * ($('.product-unit').width() + 30);
			$('.related-block').width(prodWidth);
			if($('.product-unit').length == 0) {
				$('.added-related-product-block').css('display','none');
			} else {
				$('.added-related-product-block').css('display','');
			}
			if($('.category-unit').length == 0) {
				$('.added-related-category-block').css('display','none');
			} else {
				$('.added-related-category-block').css('display','');
			}
		},

		addrelatedProduct: function(elementIndex, product) {
			$('.search-block .errorField').css('display', 'none');
			$('.search-block input.search-field').removeClass('error-input');
			if(!product) {
				var product = admin.searcharray[elementIndex];
			}

			if (product.category_url.charAt(product.category_url.length-1) == '/') {
				product.category_url = product.category_url.slice(0,-1);
			}

			var html = AvitoModule.rowRelatedProduct(product);
			$('.added-related-product-block .product-unit[data-id='+product.id+']').remove();
			$('.related-wrapper .added-related-product-block').prepend(html);
			AvitoModule.widthRelatedUpdate();
			AvitoModule.msgRelated();
			$('input[name=searchcat]').val('');
			$('.select-product-block').hide();
			$('.fastResult').hide();
		},

		rowRelatedProduct: function(product) {
			var price = (product.real_price) ? product.real_price : product.price;

			var html = '\
			<div class="product-unit" data-id='+ product.id +' data-code="'+ product.code +'">\
				<div class="product-img" style="text-align:center;height:50px;">\
					<a role="button" href="javascript:void(0);"><img src="' + product.image_url + '" style="height:50px;"></a>\
					<a class="remove-img fa fa-trash tip remove-added-product" href="javascript:void(0);" aria-hidden="true" data-hasqtip="88" oldtitle="'+lang.DELETE+'" title="" aria-describedby="qtip-88"></a>\
				</div>\
				<a href="' + mgBaseDir + '/' + product.category_url + "/" + product.product_url +
					'" data-url="' + product.category_url +
					"/" + product.product_url + '" class="product-name" target="_blank" title="' +
					product.title + '">' +
					product.title + '</a>\
				<span>' + price +' '+ admin.CURRENCY+'</span>\
			</div>\
			';
			return html;
		},

		msgRelated: function() {
			if($('.added-related-product-block .product-unit').length==0&&$('.added-related-category-block .category-unit').length==0) {
				if ($('a.add-related-product.in-block-message').length==0) {
				$('.related-wrapper .added-related-product-block').append('\
				 <a class="add-related-product in-block-message" href="javascript:void(0);"><span></span></a>\
			 ');
				}
				$('.added-related-product-block').width('800px');
			}else {
				$('.added-related-product-block .add-related-product').remove();
			};
			if ($('.added-related-category-block .category-unit').length==0) {
				$('.add-related-product-block .add-related-category.in-block-message').hide();
			} else {
				$('.add-related-product-block .add-related-category.in-block-message').show();
			}
		},

		getRelatedProducts: function() {
			var result = '';
			$('.add-related-product-block .product-unit').each(function() {
				result += $(this).data('code') + ',';
			});
			result = result.slice(0, -1);

			return result;
		},

		drawRelatedProduct: function(relatedArr) {
			$('.related-block').html('');
			$('.related-block').hide();
			relatedArr.forEach(function (product, index, array) {
				var html = AvitoModule.rowRelatedProduct(product);
				$('.related-wrapper .added-related-product-block').append(html);
				AvitoModule.widthRelatedUpdate();
			});
			AvitoModule.msgRelated();
		},

		drawCheckbox: function(resp, name) {
			if (resp == 'true') {
				$(".editOld input[name="+name+"]").prop('checked', true);
			}
			else {
				$(".editOld input[name="+name+"]").prop('checked', false);
			}
		},

		updateLink: function(name) {
			$('#ymlLink').attr('href', $('#ymlLink').attr('defaul')+name);
			$('#ymlLink').text($('#ymlLink').attr('defaul')+name);
			$('#downloadLink').attr('href', $('#downloadLink').attr('defaul')+name);
		},
	};
})();
	

(function($){
    $.widget( "ui.combobox", $.ui.autocomplete, 
    	{
    		options: { 
    			/* override default values here */
    			minLength: 2,
    			/* the argument to pass to ajax to get the complete list */
    			ajaxGetAll: {get: "all"},
    			/* you can specify the field to use as a label decorator, 
    			 * it's appended to the end of the label and is excluded 
    			 * from pattern matching.    
    			 */
    			decoratorField: null
    		},
    		
    		_create: function(){
    			if (this.element.is("SELECT")){
    				this._selectInit();
    				return;
    			}
    			
    			$.ui.autocomplete.prototype._create.call(this);
                var input = this.element;
    			input.addClass( "ui-widget ui-widget-content ui-corner-left" ).attr('type', 'text')
    			     .click(function(){ this.select(); });
    			
    			this.button = $( "<button type='button'><i class=\"fa fa-caret-down\" aria-hidden=\"true\"></i></button>" )
                .attr( "tabIndex", -1 )
                .insertAfter( input )
                .button({
                	disabled: true, // to be enabled when the menu is ready.
                    // icons: { primary: "ui-icon-triangle-1-s" },
                    text: false
                })
                .removeClass( "ui-corner-all" )
                .addClass( "ui-corner-right ui-button-icon" )
                .click(function(event) {
                    // when user clicks the show all button, we display the cached full menu
                    var data = input.data("ui-combobox");
                    clearTimeout( data.closing );
                    if (!input.isFullMenu){
                    	data._swapMenu();
                    }
                    /* input/select that are initially hidden (display=none, i.e. second level menus), 
                       will not have position cordinates until they are visible. */
                    input.combobox( "widget" ).css( "display", "block" ).css('width', input.outerWidth())
                    .position($.extend({ of: input },
                    	data.options.position
                    	));
                    input.focus();
                    data._trigger( "open" );
                    // containers such as jquery-ui dialog box will adjust it's zIndex to overlay above other elements.
                    // this becomes a problem if the combobox is inside of a dialog box, the full drop down will show
                    // under the dialog box.
                    // if (input.combobox( "widget" ).zIndex() <= input.parent().zIndex()){
                    // 	input.combobox( "widget" ).zIndex(input.parent().zIndex()+1);	
                    // }
                });
    			input.combobox( "widget" ).css('width', input.outerWidth());
    			/* to better handle large lists, put in a queue and process sequentially */
    			$(document).queue(function(){
    				var data = input.data("ui-combobox");
    				
    				if ($.isArray(data.options.source)){ 
    				    $.ui.combobox.prototype._renderFullMenu.call(data, data.options.source);
    				}else if (typeof data.options.source === "string") {
                        $.getJSON(data.options.source, data.options.ajaxGetAll , function(source){
                        	$.ui.combobox.prototype._renderFullMenu.call(data, source);
                        });
                    }else {
                    	$.ui.combobox.prototype._renderFullMenu.call(data, data.source());
    				}
    			});
    		},
    		
    		/* initialize the full list of items, this menu will be reused whenever the user clicks the show all button */
    		_renderFullMenu: function(source){
    			var self = this,
    			    input = this.element,
                    ul = input.data( "ui-combobox" ).menu.element,
                    lis = [];

    			source = this._normalize(source); 
                input.data( "ui-combobox" ).menuAll = input.data( "ui-combobox" ).menu.element.clone(true).appendTo("body")[0];
                for(var i=0; i<source.length; i++){
                	var item = source[i],
                	    label = item.label;
                	if (this.options.decoratorField != null){
                        var d = item[this.options.decoratorField] || (item.option && $(item.option).attr(this.options.decoratorField));
                        if (d != undefined)
                            label = label + " " + d;
                    }
                    lis[i] = "<li class=\"ui-menu-item\" role=\"menuitem\"><a class=\"ui-corner-all\" tabindex=\"-1\">"+label+"</a></li>";
                }
                ul[0].innerHTML = lis.join("");
                this._resizeMenu();
                ul.css('width', input.outerWidth());
                var items = $("li", ul).on("mouseover", "mouseout", function( event ) {
                	if (event.type == "mouseover"){
                		self.menu.focus( event, $(this));
                	} else {
                		self.menu.blur();
                	}
                });
                for(var i=0; i<items.length; i++){
                    $(items[i]).data( "ui-autocomplete-item", source[i]);
                }
                input.isFullMenu = true;
                this._swapMenu();
                // full menu has been rendered, now we can enable the show all button.
                self.button.button("enable");
                setTimeout(function(){
                	$(document).dequeue();
                }, 0);
    		},

    		_resizeMenu: function() {
			    var ul = this.menu.element;
			    var input = this.element;
                var data = input.data("ui-combobox");
                ul.outerWidth(this.element.outerWidth()).position($.extend({ of: input }));
                input.combobox( "widget" ).position($.extend({ of: input },	data.options.position));
                this._resizMenu();
			},

    		_resizMenu: function() {
			    var ul = this.menu.element;
			    var input = this.element;
                var data = input.data("ui-combobox");
                ul.outerWidth(this.element.outerWidth()).position($.extend({ of: input }));
                input.combobox( "widget" ).position($.extend({ of: input },	data.options.position));
			},
    		
    		/* overwrite. make the matching string bold and added label decorator */
    		_renderItem: function( ul, item ) {
                var label = item.label.replace( new RegExp(
                	"(?![^&;]+;)(?!<[^<>]*)(" + $.ui.autocomplete.escapeRegex(this.term) + 
                    ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<strong>$1</strong>" );
                if (this.options.decoratorField != null){
                	var d = item[this.options.decoratorField] || (item.option && $(item.option).attr(this.options.decoratorField));
                	if (d != undefined){
                		label = label + " " + d;
                	}
                }
                return $( "<li></li>" )
                    .data( "ui-autocomplete-item", item )
                    .append( "<a>" + label + "</a>" )
                    .appendTo( ul );

            },
            
            close: function() {
               if (this.element.isFullMenu) {
            	   this._swapMenu();
               }
               // super()
               $.ui.autocomplete.prototype.close.call(this);
            },
            
            set: function(val) {
                this.element.val(val);
                var item = this.element.children("option:selected");
                this.input.val(item.text());
            },
            
            /* overwrite. to cleanup additional stuff that was added */
            destroy: function() {
            	if (this.element.is("SELECT")){
            		this.input.removeData();
            		this.input.remove();
            		this.element.removeData().show();
            		return;
            	}
            	// super()
                $.ui.autocomplete.prototype.destroy.call(this);
            	// clean up new stuff
                this.element.removeClass( "ui-widget ui-widget-content ui-corner-left" );
                this.button.remove();
            },
            
            /* overwrite. to swap out and preserve the full menu */ 
            search: function( value, event){
            	var input = this.element;
                if (input.isFullMenu){
                	this._swapMenu();
                }
                $.ui.autocomplete.prototype.search.call(this, value, event);
            },
            
            _change: function( event ){
            	if ( !this.selectedItem ) {
                    var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( this.element.val() ) + "$", "i" ),
                        match = $.grep( this.options.source, function(value) {
                            return matcher.test( value.label );
                        });
                    if (match.length){
                    	if (match[0].option != undefined) match[0].option.selected = true;
                    }else {
                        // remove invalid value, as it didn't match anything
                        if (this.options.selectElement) {
                        	var firstItem = this.options.selectElement.children("option:first");
                            this.element.val(firstItem.text());
                        	firstItem.prop("selected", true);
                        }else {
                        	this.element.val( "" );
                        }
                        $(event.target).data("ui-combobox").previous = null;  // this will force a change event
                    }
                }                
            	// super()
            	$.ui.autocomplete.prototype._change.call(this, event);
            },
            
            _swapMenu: function(){
            	var input = this.element, 
            	    data = input.data("ui-combobox"),
            	    tmp = data.menuAll;
                data.menuAll = data.menu.element.hide()[0];
                data.menu.element[0] = tmp;
                input.isFullMenu = !input.isFullMenu;
            },
            
            /* build the source array from the options of the select element */
            _selectInit: function(){
                var select = this.element,
                    selectClass = select.attr("class"),
                    selectStyle = select.attr("style"),
                    selected = select.children( ":selected" ),
                    value = $.trim(selected.text());
                select.hide();
                this.options.source = select.children( "option" ).map(function() {
                    return { label: $.trim(this.text), option: this };
                }).toArray();
                var userSelectCallback = this.options.select;
                var userSelectedCallback = this.options.selected;
                this.options.select = function(event, ui){
                   	ui.item.option.selected = true;
                   	select.change();
                   	if (userSelectCallback) userSelectCallback(event, ui);
                   	// compatibility with jQuery UI's combobox.
                   	if (userSelectedCallback) userSelectedCallback(event, ui);
                };
                this.options.selectElement = select;
                this.input = $( "<input>" ).insertAfter( select ).val( value );
                if (selectStyle){
                	this.input.attr("style", selectStyle);
                }
                if (selectClass){
                	this.input.attr("class", selectClass);
                }
                this.input.combobox(this.options);
            },
            inputbox: function(){
            	if (this.element.is("SELECT")){
    				return this.input;
    			}else {
    				return this.element;
    			}
            }
    	}
    );
})(jQuery);