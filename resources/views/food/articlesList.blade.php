<div>
    Platos<br><br>
    <ul>
        <li id="article-@{{ article.id}}" ng-repeat="article in articles">
            Id: <span class="type">@{{ article.id}}</span><br/>
            Nombre: <span class="type">@{{ article.name}}</span><br/>
            Descripcion: <span class="type">@{{ article.description}}</span><br/>
            Entradas:<br/>
            <table>
                <tr>
                    <td>Valor</td>
                    <td>Codigo</td>
                    <td>Descripcion</td>
                </tr>
                <tr ng-repeat="item in article.attributes.entradas">
                    <td> @{{item.valor}} </td> <td> @{{item.codigo}} </td><td> @{{item.descripcion}} </td>
                </tr>
            </table>
            Fuertes:<br/>
            <table>
                <tr>
                    <td>Valor</td>
                    <td>Codigo</td>
                    <td>Descripcion</td>
                </tr>
                <tr ng-repeat="item in article.attributes.plato">
                    <td> @{{item.valor}} </td> <td> @{{item.codigo}} </td><td> @{{item.descripcion}} </td>
                </tr>
            </table>
            <br/><a href="javascript:;" ng-click="editDish(article)" class="editar">Editar</a>
        </li>
        <li ng-show="showMore">
            <button ng-click="getArticles()">Cargar mas</button>
        </li>
    </ul>
</div>
<div>
    
</div>


