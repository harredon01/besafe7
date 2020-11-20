<div class="col-lg-3 col-12">
    <div class="myaccount-tab-menu nav">
        <a href="#dashboad" class="active" data-toggle="tab"><i class="fas fa-tachometer-alt"></i>
            Dashboard</a>

        <a href="/user/payments"><i class="fa fa-cart-arrow-down"></i> Mis pagos</a>

        <a href="/user/editPassword" ><i class="fas fa-lock"></i> Actualizar contraseña</a>

        <a href="#payment-method" data-toggle="tab" style="display:none"><i class="fa fa-credit-card"></i> Payment
            Method</a>

        <a href="/user/editAddress"><i class="fa fa-map-marker"></i> Direcciones</a>

        <a href="/user/editProfile"><i class="fa fa-user"></i> Mi cuenta</a>

        <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a>

        <form id="logout-form" action="/logout" method="POST" style="display: none;" class="ng-pristine ng-valid">
            <input type="hidden" name="_token" value="nUU11xTUckbo3YQVzsfDv2TSenTKiLH4jnqg1Xx5">
        </form>
    </div>
</div>

