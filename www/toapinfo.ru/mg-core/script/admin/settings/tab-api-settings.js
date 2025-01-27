var settings_api = (function () {
	return {
		editMode: undefined,
		item: undefined,
		
		init: function() {
			// Сохранение настроек API
			$('#tab-api-settings').on('click', '.save-button', function() {
				settings_api.saveApi(true);
			});

			// Генерация токена API
			$('#tab-api-settings').on('click', '.createToken', function() {
				settings_api.createToken();
			});

			// Открытие модали для создания токена
			$('#tab-api-settings').on('click', '.addToken', function() {
				settings_api.editMode = 'add';
				$('#tab-api-settings input').val('');
				$('#tab-api-settings #token-edit-modal input').removeClass('error-input');
				admin.openModal('#token-edit-modal');
			});

			// удаление токена
			$('#tab-api-settings').on('click', '.fa-trash', function() {
				if(confirm(lang.CONFIRM_TOKEN_DEL)) {
					$(this).parents('.token-item').detach();
					settings_api.saveApi(false);
				}
			});

			// редактирование токена
			$('#tab-api-settings').on('click', '.fa-pencil', function() {
				settings_api.editMode = 'edit';
				settings_api.item = $(this).parents('.token-item');
				$('#tab-api-settings input').val('');
				$('#tab-api-settings [name=name]').val(admin.htmlspecialchars_decode(settings_api.item.find('.name').html()));
				$('#tab-api-settings [name=token]').val(settings_api.item.find('.token').html());
				$('#tab-api-settings [name=key]').val(admin.htmlspecialchars_decode(settings_api.item.find('.key').html()));
				admin.openModal('#token-edit-modal');
			});
		},
		saveApi: function(errorCheck, closeModal) {
			closeModal = typeof closeModal !== 'undefined' ? closeModal : true;
			errorCheck = typeof errorCheck !== 'undefined' ? errorCheck : true;
			if(errorCheck) {
				var error = false;
				$('#tab-api-settings #token-edit-modal input').each(function() {
					if($(this).val() == '') {
						$(this).addClass('error-input');
						error = true;
					} else {
						$(this).removeClass('error-input');
					}
				});
				if(error) return false;
			}
		  	var data = {};
		  	if(settings_api.editMode == 'add') {
		  		$('#tab-api-settings .toDel').detach();
		  		settings_api.addRow();
		  	} 
		  	if(settings_api.editMode == 'edit') {
		  		settings_api.item.find('.name').html(admin.htmlspecialchars($('#tab-api-settings [name=name]').val()));
		  		settings_api.item.find('.token').html($('#tab-api-settings [name=token]').val());
		  		settings_api.item.find('.key').html(admin.htmlspecialchars($('#tab-api-settings [name=key]').val()));
		  	}
		  	$('.tokens-list .token-item').each(function(index) {
		  		if(($(this).find('.name').html() != undefined)&&($(this).find('.name').html() != '')) {
		  			data[index] = {};
		  			data[index]['name'] = $(this).find('.name').html();
		  			data[index]['token'] = $(this).find('.token').html();
		  			data[index]['key'] = $(this).find('.key').html();
		  		}
		  	});
		  	admin.ajaxRequest({
		    	mguniqueurl: "action/saveApi", // действия для выполнения на сервере    
		    	data: data         
		  	},      
		  	function(response) {
		    	admin.indication(response.status, response.msg);
					if (closeModal) {
						admin.closeModal('#token-edit-modal');
						admin.refreshPanel();     
					} else {
						$('#token-edit-modal').attr('data-refresh', 'true');
					}
		  	});
		},

		createToken: function() {
		  	admin.ajaxRequest({
		    	mguniqueurl: "action/createToken", // действия для выполнения на сервере         
		  	},      
		  	function(response) {
		    	$('#tab-api-settings input[name=token]').val(response.data);
		  	});
		},

		addRow: function() {
			$('.tokens-list').prepend('\
				<tr class="token-item">\
				  <td class="name">'+admin.htmlspecialchars($('#tab-api-settings [name=name]').val())+'</td>\
				  <td class="token">'+$('#tab-api-settings [name=token]').val()+'</td>\
				  <td class="key">'+admin.htmlspecialchars($('#tab-api-settings [name=key]').val())+'</td>\
				  <td class="text-right action-list">\
				    <a role="button" href="javascript:void(0);" class="fa fa-pencil" style="color:#444;margin-right:5px;"></a>\
				    <a role="button" href="javascript:void(0);" class="fa fa-trash"></a>\
				  </td>\
				</tr>');
		},
	};
})();