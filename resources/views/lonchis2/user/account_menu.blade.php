<div class="col-sm-12 col-md-3 col-lg-3">
    <!-- Nav tabs -->
    <div class="dashboard_tab_button">
        <ul role="tablist" class="nav flex-column dashboard-list" id="nav-tab">
            <li><a href="#dashboard"  class="nav-link active">Dashboard</a></li>
            <li> <a href="/user/payments" class="nav-link">Mis pagos</a></li>
            <li><a href="/user/editPassword" class="nav-link">Actualizar contraseña</a></li>
            <li><a href="/user/editAddress" class="nav-link">Mis Direcciones</a></li>
            <li><a href="/user/editProfile" class="nav-link">Mi Cuenta</a></li>
            
            <li  ng-if="hasMerchants"><a href="/user/merchants/orders"  class="nav-link active">Ordenes</a></li>
            <li ng-if="hasMerchants"> <a href="/admin/store/global" class="nav-link">Contenido</a></li>
            <li ng-if="user.id < 78"><a href="/admin/zones" class="nav-link">Cobertura</a></li>
            <li ng-if="user.id < 78"><a href="/billing/payments" class="nav-link">Pagos Globales</a></li>
            <li ng-if="user.id < 78"><a href="/billing/orders" class="nav-link">Ordenes</a></li>
            <li ng-if="user.id < 78"><a href="/food/menu" class="nav-link">Menu</a></li>
            <li ng-if="user.id < 78"><a href="/food/routes" class="nav-link">Rutas</a></li>
            <li ng-if="user.id < 78"><a href="/food/deliveries" class="nav-link">Entregas</a></li>
            <li ng-if="user.id < 78"><a href="/food/largest_addresses" class="nav-link">Direcciones comunes</a></li>
            <li ng-if="user.id < 78"><a href="/admin/store/global-admin" class="nav-link">Import Global</a></li>

            <li><a href="/logout" onclick="event.preventDefault();document.cookie = 'shippingAddress= ; expires = Thu, 01 Jan 1970 00:00:00 GMT'; document.getElementById('logout-form').submit();" class="nav-link">logout</a></li>
        </ul>
        <form id="logout-form" action="/logout" method="POST" style="display: none;" class="ng-pristine ng-valid">
            @csrf
        </form>
    </div>    
</div>

