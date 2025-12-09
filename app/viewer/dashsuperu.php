<?php
    require_once './app/sesiones/session.php';
    $obj = new Session();
    $sesion = $obj->getSession();
    
    if (!isset($sesion['idusuario']) || $sesion['rol'] !== 'super') {
        header('Location: /login');
        exit;
    }
?>

<?php include './app/viewer\plantillasSusuario\headSusuario.php'; ?>
<?php include './app/viewer\plantillasSusuario\headerSusuario.php'; ?>

<style>
    /* Animación de entrada */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .main-wrapper {
        min-height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
        justify-content: center; 
        padding-bottom: 40px;
    }

    .welcome-section {
        text-align: center;
        margin-top: 30px;
        margin-bottom: 50px;
        animation: fadeInUp 0.8s ease-out;
    }
    
    .welcome-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: white;
        margin-bottom: 10px;
    }
    
    .gradient-text {
        background: linear-gradient(135deg, var(--calypso) 0%, var(--blue-500) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    /* --- GRID DE 2 COLUMNAS PARA SUPERUSUARIO --- */
    .dash-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr); 
        gap: 25px;
        max-width: 900px; 
        margin: 0 auto;
        padding: 0 30px;
        animation: fadeInUp 1s ease-out;
        width: 100%;
    }
    
    /* --- TARJETA RECTANGULAR HORIZONTAL --- */
    .glass-card {
        background: rgba(30, 41, 59, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 20px;
        display: flex;
        flex-direction: row; 
        align-items: center; 
        padding: 25px;
        gap: 20px;
        
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(12px);
        position: relative;
        overflow: hidden;
        min-height: 130px; 
    }
    
    .glass-card:hover {
        transform: translateY(-6px);
        background: rgba(30, 41, 59, 0.85);
        border-color: rgba(24, 197, 163, 0.5); 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
    }
    
    .card-icon {
        width: 55px;
        height: 55px;
        border-radius: 14px;
        background: rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        color: var(--calypso);
        transition: 0.4s;
        flex-shrink: 0;
    }
    
    .glass-card:hover .card-icon {
        background: var(--gradient-primary);
        color: #07182a;
        transform: scale(1.1) rotate(-5deg);
        box-shadow: 0 0 20px rgba(24,197,163,0.3);
    }
    
    .card-content {
        flex-grow: 1;
    }

    .card-content h3 {
        margin: 0 0 4px 0;
        color: white;
        font-size: 1.3rem;
        font-weight: 700;
    }
    
    .card-content p {
        margin: 0;
        color: #94a3b8;
        font-size: 0.85rem;
        line-height: 1.3;
    }
    
    .card-arrow {
        color: rgba(255,255,255,0.15);
        font-size: 1.2rem;
        transition: 0.3s;
        margin-left: auto;
    }
    
    .glass-card:hover .card-arrow {
        color: var(--calypso);
        transform: translateX(5px);
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 768px) {
        .dash-grid {
            grid-template-columns: 1fr;
        }
        .glass-card {
            padding: 20px;
        }
    }
</style>

<div class="main-wrapper">
    
    <div class="welcome-section">
        <h1 class="welcome-title">Hola, <span class="gradient-text"><?php echo htmlspecialchars($sesion['nombre']); ?></span></h1>
        <p style="color:var(--muted); font-size:1.1rem;">Panel de Gestión Ministerial - Selecciona un módulo para administrar.</p>
    </div>

    <div class="dash-grid">
        
        <div class="glass-card" onclick="window.location.href='/actividadessuperu'">
            <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="card-content">
                <h3>Actividades</h3>
                <p>Calendario de eventos y reuniones.</p>
            </div>
            <i class="fas fa-arrow-right card-arrow"></i>
        </div>

        <div class="glass-card" onclick="window.location.href='/alabanzassuperu'">
            <div class="card-icon"><i class="fas fa-music"></i></div>
            <div class="card-content">
                <h3>Alabanzas</h3>
                <p>Repertorio musical y partituras.</p>
            </div>
            <i class="fas fa-arrow-right card-arrow"></i>
        </div>

        <div class="glass-card" onclick="window.location.href='/oracionessuperu'">
            <div class="card-icon"><i class="fas fa-praying-hands"></i></div>
            <div class="card-content">
                <h3>Peticiones de Oración</h3>
                <p>Gestión de motivos de oración.</p>
            </div>
            <i class="fas fa-arrow-right card-arrow"></i>
        </div>

        <div class="glass-card" onclick="window.location.href='/sermonessuperu'">
            <div class="card-icon"><i class="fas fa-church"></i></div>
            <div class="card-content">
                <h3>Sermones</h3>
                <p>Series y predicaciones bíblicas.</p>
            </div>
            <i class="fas fa-arrow-right card-arrow"></i>
        </div>

    </div>
</div>

</body>
</html>