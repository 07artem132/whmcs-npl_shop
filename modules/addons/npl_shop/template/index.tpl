<table border="1" class="table table-striped table-bordered table-hover table-condensed"  style="width: 100%;white-space:pre;">
    <thead style="text-align: center;">
        <tr>
            <td>ID лицензии</td> <td>ID пользователя</td> <td style="width: 686px;">Содержимое npl лицензии</td><td>Сколько раз было скачано</td> <td>Ссылка на скачивание</td> <td>Ссылка для удаления</td> <td>Дата добавления NPL в систему</td> 
        </tr>
    </thead>
    <tbody>
        {foreach from=$npl_list item=npl}
            <tr>
                {foreach from=$npl item=info}
                    <td>{$info|truncate:700:"Ключ скрыт"}</td>
                {/foreach}
            </tr>
        {/foreach}
    </tbody>
</table>