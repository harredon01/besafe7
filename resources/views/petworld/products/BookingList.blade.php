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
            <span ng-show="item.attributes.name">@{{ item.attributes.name}}</span>
            <span ng-show="!item.attributes.name">@{{ item.name}}</span>
        </td>
        <td class="col-md-3 text-black" ng-hide="item.pending">
            @{{ item.attributes.name}} @{{ item.attributes.from | date:'medium'}}
        </td>
        <td class="col-md-2" ng-show="item.pending">                              
            <a href="javascript:;" class="text-orange" ng-click="programItem(item)" style="border: 2px solid #ff7c00;border-radius: 5px;padding: 0 10px;">Programar</a>
        </td>
        <td class="col-md-2 text-black" ng-hide="item.pending">
            
        </td>
    </tr>
</table>

