<div>
    Platos<br><br>
    <ul>
        <li id="article-@{{ article.id}}" ng-repeat="article in articles">
            Id: <span class="type">@{{ article.id}}</span><br/>
            Nombre: <span class="type">@{{ article.name}}</span><br/>
            Descripcion: <span class="type">@{{ article.description}}</span><br/>
            Entradas:<br/>
            <table>
                <tr ng-repeat="(key, value) in item.attributes.entradas">
                    <td> @{{key}} </td> <td> @{{ value}} </td>
                </tr>
            </table>
            Fuertes:<br/>
            <table>
                <tr ng-repeat="(key, value) in item.attributes.plato">
                    <td> @{{key}} </td> <td> @{{ value}} </td>
                </tr>
            </table>
            <select>
                <option value="Programado">Programado</option>
                <option value="Transit">En transito</option>
                <option value="Delivered">Entregado</option>
            </select>
            <br/><a href="javascript:;" ng-click="editDish(article)" class="editar">Aprobar</a>
        </li>
        <li ng-show="showMore">
            <button ng-click="getArticles()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


