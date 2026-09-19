<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar sesión · TPV Panadería</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-decoration">
            <div class="login-decoration-content">
                <div style="font-size: 80px; margin-bottom: 16px;">🥖🥐🧁</div>
                <h1>TPV Panadería</h1>
                <p>El programa de gestión que tu panadería, pastelería o confitería necesita.</p>
                <div class="login-features">
                    <div class="login-feature">
                        <div class="login-feature-icon">🛒</div>
                        <h4>Venta rápida</h4>
                        <p>Mostrador ágil con tickets instantáneos</p>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon">📦</div>
                        <h4>Control de stock</h4>
                        <p>Existencias, mermas y reposición</p>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon">🧁</div>
                        <h4>Productos elaborados</h4>
                        <p>Recetas, ingredientes y formatos</p>
                    </div>
                    <div class="login-feature">
                        <div class="login-feature-icon">📊</div>
                        <h4>Informes detallados</h4>
                        <p>Decisiones con mejor información</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-hero">
            <div class="login-card">
                <div class="login-logo">🥖</div>
                <h2 class="login-title">Bienvenido de vuelta</h2>
                <p class="login-subtitle">Inicia sesión para acceder a tu panadería</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                {{ $error }}<br>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf
                    <div class="input-icon-group">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control"
                               placeholder="Correo electrónico" value="{{ old('email', 'admin@panaderia.com') }}"
                               autofocus required>
                    </div>

                    <div class="input-icon-group">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control"
                               placeholder="Contraseña" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); cursor: pointer;">
                            <input type="checkbox" name="remember" style="width: 16px; height: 16px;">
                            Recordarme
                        </label>
                        <a href="#" style="font-size: 13px;">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>&nbsp;&nbsp;Iniciar sesión
                    </button>
                </form>

                <div style="text-align: center; margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border-color); font-size: 12px; color: var(--text-muted);">
                    <p style="margin: 0 0 8px;">Credenciales de demostración:</p>
                    <code style="background: var(--bg-page); padding: 4px 10px; border-radius: 6px;">admin@panaderia.com / panaderia123</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
