<div>
    Pagos<br><br>
    <ul>
        <li id="payment-@{{ payment.id}}" ng-repeat="payment in payments">
            Id: <span class="type">@{{ payment.id}}</span><br/>
            Status: <span class="type">@{{ payment.status}}</span><br/>
            Ultima actualizacion: <span class="type">@{{ payment.updated_at}}</span><br/>
            Creado: <span class="type">@{{ payment.created_at}}</span><br/>
            Referencia: <span class="type">@{{ payment.referenceCode}}</span><br/>
            Total: <span class="type">@{{ payment.total}}</span><br/>

            <br/><a href="javascript:;" ng-click="approvePayment(payment.id)" class="editar">Aprobar Pago</a>
        </li>
        <li ng-show="showMore">
            <button ng-click="getPayments()">Cargar mas</button>
        </li>
    </ul>
</div>


