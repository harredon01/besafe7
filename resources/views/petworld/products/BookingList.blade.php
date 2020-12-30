<table >
    <tr>
        <th class="col-md-3 text-black">
            Nombre
        </th>
        <th class="col-md-2">

        </th>
    </tr>
    <tr ng-repeat="item in appointments">
        <td class="col-md-3 text-black" ng-show="item.pending">
            @{{ item.attributes.name}}
        </td>
        <td class="col-md-3 text-black" ng-hide="item.pending">
            @{{ item.attributes.name}} @{{ item.attributes.from | date:'medium'}}
        </td>
        <td class="col-md-2" ng-show="item.pending">                              
            <a href="javascript:;" class="text-orange" ng-click="programItem(item)">Programar</a>
        </td>
        <td class="col-md-2 text-black" ng-hide="item.pending">                              
            
        </td>
    </tr>
</table>

