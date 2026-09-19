<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🥖</div>
        <div>
            <div class="brand-name">{{ Str::limit($appConfig->nombre_empresa ?? 'TPV Panadería', 18) }}</div>
            <div class="brand-sub">Punto de Venta</div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="sidebar-section">Principal</li>
        <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Dashboard
        </a></li>
        <li><a href="{{ route('tpv.index') }}" class="{{ request()->routeIs('tpv.*') ? 'active' : '' }}">
            <i class="fas fa-cash-register"></i> Punto de Venta
        </a></li>

        <li class="sidebar-section">Catálogo</li>
        <li><a href="{{ route('productos.index') }}" class="{{ request()->routeIs('productos.*') ? 'active' : '' }}">
            <i class="fas fa-bread-slice"></i> Productos
        </a></li>
        <li><a href="{{ route('categorias.index') }}" class="{{ request()->routeIs('categorias.*') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Categorías
        </a></li>
        <li><a href="{{ route('stock.index') }}" class="{{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i class="fas fa-boxes-stacked"></i> Stock y Mermas
        </a></li>

        <li class="sidebar-section">Contactos</li>
        <li><a href="{{ route('clientes.index') }}" class="{{ request()->routeIs('clientes.*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Clientes
        </a></li>
        <li><a href="{{ route('proveedores.index') }}" class="{{ request()->routeIs('proveedores.*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i> Proveedores
        </a></li>

        <li class="sidebar-section">Operaciones</li>
        <li><a href="{{ route('documentos.index') }}" class="{{ request()->routeIs('documentos.*') ? 'active' : '' }}">
            <i class="fas fa-file-invoice"></i> Documentos
        </a></li>
        <li><a href="{{ route('sunat.index') }}" class="{{ request()->routeIs('sunat.*') ? 'active' : '' }}">
            <i class="fas fa-file-signature"></i> SUNAT - Electrónicos
        </a></li>
        <li><a href="{{ route('compras.index') }}" class="{{ request()->routeIs('compras.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i> Compras
        </a></li>
        <li><a href="{{ route('informes.index') }}" class="{{ request()->routeIs('informes.*') ? 'active' : '' }}">
            <i class="fas fa-chart-pie"></i> Informes
        </a></li>
        <li><a href="{{ route('horarios.index') }}" class="{{ request()->routeIs('horarios.*') ? 'active' : '' }}">
            <i class="fas fa-clock"></i> Control horario
        </a></li>

        @if(auth()->user()?->esEncargado())
        <li class="sidebar-section">Sistema</li>
        <li><a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <i class="fas fa-user-shield"></i> Usuarios
        </a></li>
        <li><a href="{{ route('configuracion.index') }}" class="{{ request()->routeIs('configuracion.*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> Configuración
        </a></li>
        <li><a href="{{ route('backup.index') }}" class="{{ request()->routeIs('backup.*') ? 'active' : '' }}">
            <i class="fas fa-database"></i> Backup y mantenimiento
        </a></li>
        @endif
    </ul>

    <div class="sidebar-footer">
        <div>v1.0 · {{ date('Y') }}</div>
        <div style="margin-top: 4px;">{{ $appConfig->nombre_empresa ?? 'TPV Panadería' }}</div>
    </div>
</aside>
