{{-- This file is used for menu items by any Backpack v6 theme --}}
<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="la la-home nav-icon"></i> {{ trans('backpack::base.dashboard') }}</a></li>

<x-backpack::menu-item title="Пользователи" icon="la la-user" :link="backpack_url('user')" />



<x-backpack::menu-item title="Курьеры" icon="la la-truck-pickup" :link="backpack_url('courier')"/>

<x-backpack::menu-item title="Виртуальные номера" icon="la la-tty" :link="backpack_url('virtual-number')"/>
<x-backpack::menu-item title="Сценарии" icon="la la-sitemap" :link="backpack_url('scenario')"/>

<x-backpack::menu-item title="Статусы" icon="la la-stream" :link="backpack_url('status')"/>

<x-backpack::menu-item title="Типы заказа" icon="la la-tags" :link="backpack_url('order-type')"/>
