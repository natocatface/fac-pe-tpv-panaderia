<header class="topbar">
    <div class="topbar-left">
        <button class="topbar-toggle"><i class="fas fa-bars"></i></button>
        <div>
            <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
            <div class="breadcrumb">@yield('breadcrumb')</div>
        </div>
    </div>

    <div class="topbar-right">
        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Buscar productos, clientes...">
        </div>

        <a href="{{ route('tpv.index') }}" class="btn btn-primary" style="height: 40px;">
            <i class="fas fa-cash-register"></i> Abrir TPV
        </a>

        <button class="topbar-icon" title="Notificaciones">
            <i class="fas fa-bell"></i>
            <span class="badge">3</span>
        </button>

        <div class="user-menu" onclick="document.getElementById('user-dropdown').classList.toggle('show')" style="position: relative;">
            <img src="{{ auth()->user()->avatarUrl() }}" alt="Usuario">
            <div class="user-menu-info">
                <div class="user-menu-name">{{ auth()->user()->name }}</div>
                <div class="user-menu-role">{{ ucfirst(auth()->user()->rol) }}</div>
            </div>
            <i class="fas fa-chevron-down" style="font-size: 11px; color: var(--text-muted);"></i>
            <div id="user-dropdown" style="display:none; position:absolute; top: 50px; right: 0; background:white; border:1px solid var(--border-color); border-radius: 10px; box-shadow: var(--shadow-md); min-width: 200px; z-index: 1000;">
                <a href="#" style="display:block; padding: 10px 16px; color: var(--text-main);">
                    <i class="fas fa-user"></i>&nbsp; Mi perfil
                </a>
                <a href="{{ route('configuracion.index') }}" style="display:block; padding: 10px 16px; color: var(--text-main);">
                    <i class="fas fa-cog"></i>&nbsp; Configuración
                </a>
                <hr style="margin: 0; border-color: var(--border-color);">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="display: block; width: 100%; text-align: left; padding: 10px 16px; background: none; border: none; cursor: pointer; color: var(--color-danger);">
                        <i class="fas fa-sign-out-alt"></i>&nbsp; Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
<style>#user-dropdown.show { display: block !important; }</style>
