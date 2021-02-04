<div>
    Pagos<br><br>
    <ul>
        <li id="payment-@{{ payment.id}}" ng-repeat="payment in payments">
            Id: <span class="type">@{{ payment.id}}</span><br/>
            Status: <span class="type">@{{ payment.status}}</span><br/>
            Ultima actualizacion: <span class="type">@{{ payment.updated_at}}</span><br/>
            Creado: <span class="type">@{{ payment.created_at}}</span><br/>
            Usuario: <span class="type">@{{ payment.user.firstName}} @{{ payment.user.lastName}}</span><br/>
            cel: <span class="type">@{{ payment.user.cellphone}}</span><br/>
            Referencia: <span class="type">@{{ payment.referenceCode}}</span><br/>
            Total: <span class="type">@{{ payment.total}}</span><br/>
            
            <a ng-hide="payment.status=='approved'" href="javascript:;" ng-click="approvePayment(payment)" class="editar">Aprobar Pago</a><br/><br/>
        </li>
        <li ng-show="loadMore">
            <button ng-click="getPayments()">Cargar mas</button>
        </li>
    </ul>
</div>


