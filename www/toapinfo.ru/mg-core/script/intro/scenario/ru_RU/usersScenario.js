var usersScenario = {
    hits:[
        {
            selector: '.section-user .add-new-button',
            message: 'Чтобы создать нового пользователя нажмите на эту кнопку. Откроется карточка пользователя, в которой можно будет указать его логин и пароль, контакты, группу и другие данные.',
        },
        {
            selector: '.section-user .show-filters',
            message: 'Пользователей можно отфильтровать по группам, телефону, email, имени, дате регистрации.',
        },
        {
            selector: '.section-user .get-csv',
            message: 'Можно скачать список пользователей в табличный файл с расширением .CSV, для переноса контактных данных в любую другую программу, например, в рассылочник писем или в CRM.',
        },
        {
            selector: '.section-user .users-group',
            message: 'Тут можно создавать группы пользователей, такие как, например, «Менеджеры» и «Оптовики». Задавать им настройки доступа, указывая какие разделы и действия будут доступны тем или иным группам пользователей.',
        },
        {
			selector: '.section-user .js-tointro-user1 tr:first',
			message: 'Таблица пользователей. Тут отображается список пользователей. Нажав на заголовок колонки можно отсортировать строки таблицы по возрастанию и убыванию.'
        },
        {
	      selector: '.section-user .open-col-config-modal',
	      message: 'Нажмите на шестеренку, чтобы настроить видимость и порядок отображения колонок в таблице. Дополнительные поля пользователей, созданные в разделе настроек, также можно выводить в эту таблицу.'
	    },
        {
			selector: '.section-user .js-tointro-user2 tr:first',
			message: 'Строка с информацией о пользователе.'
		},
		{
			selector: '.section-user .js-tointro-user2 tr:first .action-list',
			message: 'В колонке «Действия» находятся элементы для управления пользователями.\
			<ul style="list-style-type: none; padding: 0px;">\
			<li><i class="fa fa-pencil" style="font-size:16px;"></i> — Открывает карточку пользователя на редактирование;</li>\
			<li><i class="fa fa-eye-slash" style="font-size:16px;"></i> — Авторизует вас на сайте под аккаунтом выбранного пользователя без ввода пароля;</li>\
			<li><i class="fa fa-cubes" style="font-size:16px;"></i> — Отображает список оплаченных товаров этим пользователем;</li>\
			<li><i class="fa fa-trash" style="font-size:16px; color:black!important;"></i> — Удаляет пользователя;</li>\
			</ul>'
		},
	    {
	      selector: '.section-user .label-select:first',
	      message: '«Массовые действия» — это те действия, которые можно применить сразу к нескольким и более отмеченным галочками пользователям в таблице. Например, можно удалить выбранных пользователей или выгрузить их в .CSV файл.'
	    },
	    {
	      selector: '.section-user .table-count-print',
	      message: 'Количество строк отображаемых в таблице. Чем больше строк тем дольше будет открываться раздел.'
	    },

	    {
	      selector: '.section-user .mg-pager',
	      message: 'Постраничная навигация позволяет пролистывать строки пользователей по страницам.'
	    },
	    {
	      selector: '.doc-link',
	      message: 'Документация. Официальная документация с видеоуроками доступна по адресу wiki.moguta.ru '
	    },

    ] 
}
