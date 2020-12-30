<nav class="sticky-navigation">
    <ul class="mainmenu sticky-menu">
        @foreach ($categories as $category)
        @if (count($category['children']) > 0)
        <li class="mainmenu__item menu-item-has-children sticky-has-child">
            <a href="javascript:;" class="mainmenu__link">{{ $category['name']}}</a>
            <ul class="sub-menu">
                @foreach ($category['children'] as $child)
                <li>
                    @if(!(strpos($child['type'], 'merchant')===false))
                    <a href="/a/merchants/{{ $child['url']}}" ng-click="goTo('{{ $child['type']}}', $event)">{{ $child['name']}}</a>
                    @else
                    <a href="/a/reports/{{ $child['url']}}" ng-click="goTo('{{ $child['type']}}', $event)">{{ $child['name']}}</a>
                    @endif
                </li>
                @endforeach
            </ul>
        </li>
        @endif
        @endforeach
        <li class="mainmenu__item menu-item-has-children sticky-has-child ">
            <a href="javascript:;" class="mainmenu__link">Participa</a>
            <ul class="sub-menu">
                <li>
                    <a href="/a/contact-us/vets">Veterinarios</a>
                </li>
                <li>
                    <a href="/a/contact-us/shops">Tiendas de mascotas</a>
                </li>
                <li>
                    <a href="/a/contact-us/lost">Mascotas Perdidas</a>
                </li>
                <li>
                    <a href="/a/contact-us/sale">Mascotas a la venta</a>
                </li>
                <li>
                    <a href="/a/contact-us/bla" >Contactanos</a>
                </li>
            </ul>
        </li>
    </ul>
    <div class="sticky-mobile-menu  d-lg-none">
        <span class="sticky-menu-btn"></span>
    </div>
</nav>