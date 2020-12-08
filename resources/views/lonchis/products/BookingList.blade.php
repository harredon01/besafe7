<table >
    <tr>
        <th class="col-md-3 text-black">
            Nombre
        </th>
        <th class="col-md-2">

        </th>
    </tr>
    <tr ng-repeat="item in appointments">
        <td class="col-md-3 text-black">
            @{{ item.name}}
        </td>
        <td class="col-md-2">                              
            <a href="javascript:;" class="text-orange" ng-click="programItem(item)">Programar</a>
        </td>
    </tr>
</table>

