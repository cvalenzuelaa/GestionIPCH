<?php
require_once __DIR__ . '/../sesiones/session.php';
$sesionObj = new Session();
$usuarioData = $sesionObj->getSession();

require_once '../models/actividadesModel.php';

// CAMBIO: Usamos $_REQUEST para aceptar GET (necesario para descargar archivos)
$accion = $_REQUEST['accion'] ?? null; 
$obj = new ActividadesModel();
$idUsuario = $usuarioData['idusuario'] ?? null;

switch ($accion) {
    case 'getResponsables':
        echo json_encode($obj->getResponsables());
        break;

    case 'insert':
        if (!$idUsuario) { echo json_encode(['error' => 'Sesión no válida.']); exit; }
        $conflictos = $obj->checkTimeConflicts($_POST['fecha'], $_POST['hora_inicio'], $_POST['hora_fin']);
        if (count($conflictos) > 0 && !isset($_POST['force_save'])) {
            echo json_encode(['conflict' => true, 'message' => '⚠️ Tope con: ' . $conflictos[0]['titulo']]);
            exit;
        }
        echo json_encode($obj->insert([
            'titulo' => $_POST['titulo'], 'descripcion' => $_POST['descripcion'], 'fecha' => $_POST['fecha'],
            'tipo' => $_POST['tipo'], 'hora_inicio' => $_POST['hora_inicio'], 'hora_fin' => $_POST['hora_fin'],
            'responsable' => $_POST['responsable'], 'recurrente' => isset($_POST['recurrente']) ? 1 : 0,
            'frecuencia' => $_POST['frecuencia'] ?? 'unica', 'idusuario' => $idUsuario
        ]));
        break;

    case 'getCalendarEvents':
        $start = $_POST['start'];
        $end = $_POST['end'];
        $events = [];

        // 1. Actividades
        $actividades = $obj->getActivitiesByRange($start, $end);
        foreach ($actividades as $act) {
            $color = '#4b5563';
            if ($act['tipo'] == 'culto') $color = '#18c5a3';
            if ($act['tipo'] == 'reunion') $color = '#2b66b3';
            if ($act['tipo'] == 'ensayo') $color = '#6d28d9';
            $startISO = date('Y-m-d', strtotime($act['fecha'])) . 'T' . $act['hora_inicio'];
            $endISO = date('Y-m-d', strtotime($act['fecha'])) . 'T' . $act['hora_fin'];
            $events[] = [
                'id' => 'act_' . $act['idactividad'], 'title' => $act['titulo'], 'start' => $startISO, 'end' => $endISO,
                'backgroundColor' => $color, 'borderColor' => 'transparent', 'allDay' => false,
                'extendedProps' => ['tipo' => 'actividad', 'desc' => $act['descripcion'], 'responsable' => ($act['resp_nombre'] ?? '') . ' ' . ($act['resp_apellido'] ?? '')]
            ];
        }

        // 2. Oraciones
        $oraciones = $obj->getApprovedPrayersByRange($start, $end);
        foreach ($oraciones as $oracion) {
            $events[] = [
                'id' => 'pray_' . $oracion['idoracion'], 'title' => '🙏 Oración: ' . $oracion['nombre'],
                'start' => date('Y-m-d', strtotime($oracion['fecha'])), 'allDay' => true,
                'backgroundColor' => '#f59e0b', 'borderColor' => 'transparent',
                'extendedProps' => ['tipo' => 'oracion', 'desc' => $oracion['descripcion'], 'solicitante' => $oracion['nombre'] . ' ' . $oracion['apellido']]
            ];
        }

        // 3. Cumpleaños
        $startMonth = (int)date('m', strtotime($start));
        $endMonth = (int)date('m', strtotime($end));
        $years = [date('Y', strtotime($start)), date('Y', strtotime($end))];
        $years = array_unique($years);
        $mesesAConsultar = range($startMonth, $endMonth);
        if($endMonth < $startMonth) $mesesAConsultar = [12, 1]; 

        foreach ($mesesAConsultar as $mes) {
            $birthdays = $obj->getBirthdaysByMonth($mes);
            foreach ($birthdays as $bday) {
                $dia = date('d', strtotime($bday['fecha_nacimiento']));
                foreach ($years as $year) {
                    $events[] = [
                        'id' => 'bd_' . $bday['idmiembro'] . '_' . $year, 'title' => '🎂 ' . $bday['nombre'],
                        'start' => "$year-$mes-$dia", 'allDay' => true, 'backgroundColor' => '#ff6b6b', 'borderColor' => 'transparent',
                        'extendedProps' => ['tipo' => 'cumpleanos']
                    ];
                }
            }
        }
        echo json_encode($events);
        break;

    case 'getMonthlySummary':
        echo json_encode($obj->getMonthlySummary($_POST['year'], $_POST['month']));
        break;
    
    case 'getAttendanceData':
        // Validamos que sea admin (usando el rol de la sesión)
        if (($usuarioData['rol'] ?? '') !== 'admin') {
            echo json_encode(['error' => 'No tienes permisos de administrador.']);
            exit;
        }

        $idactividad = $_POST['idactividad'];
        
        // Obtenemos miembros y el estado actual de la asistencia
        $miembros = $obj->getMembersForAttendance();
        $asistenciaActual = $obj->getAttendanceByActivity($idactividad);

        echo json_encode([
            'miembros' => $miembros,
            'asistencia' => $asistenciaActual
        ]);
        break;

    case 'saveAttendance':
        if (($usuarioData['rol'] ?? '') !== 'admin') {
            echo json_encode(['error' => 'No tienes permisos.']);
            exit;
        }

        $idactividad = $_POST['idactividad'];
        // Recibimos la lista como JSON string y la decodificamos
        $lista = json_decode($_POST['lista'], true); 

        echo json_encode($obj->saveAttendance($idactividad, $lista));
        break;
    // =========================================================
    // NUEVO CASO: EXPORTAR A EXCEL
    // =========================================================
    case 'exportExcel':
        $year = $_GET['year'];
        $month = $_GET['month'];
        
        $actividades = $obj->getActivitiesReport($year, $month);
        $cumpleanos = $obj->getBirthdaysReport($month);
        $oraciones = $obj->getPrayersReport($year, $month);

        $nombreMes = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $filename = "Resumen_IPCH_" . $nombreMes[(int)$month] . "_" . $year . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$filename");
        header("Pragma: no-cache");
        header("Expires: 0");

        // Construcción de la tabla HTML para Excel
        $th = "background-color:#18c5a3; color:white; border:1px solid #000; padding:10px;";
        $td = "border:1px solid #000; padding:8px;";
        
        echo "<meta charset='UTF-8'>";
        echo "<table border='1'>";
        
        // TÍTULO GENERAL
        echo "<tr><td colspan='6' style='background-color:#2b66b3; color:white; font-size:16px; font-weight:bold; text-align:center;'>RESUMEN MENSUAL: " . strtoupper($nombreMes[(int)$month]) . " $year</td></tr>";
        echo "<tr><td colspan='6'></td></tr>";

        // SECCIÓN ACTIVIDADES
        echo "<tr><td colspan='6' style='background-color:#eee; font-weight:bold;'>📅 ACTIVIDADES PROGRAMADAS</td></tr>";
        echo "<tr><th style='$th'>Fecha</th><th style='$th'>Horario</th><th style='$th'>Título</th><th style='$th'>Tipo</th><th style='$th'>Responsable</th><th style='$th'>Descripción</th></tr>";
        
        if(empty($actividades)) { echo "<tr><td colspan='6' align='center'>Sin actividades</td></tr>"; }
        foreach($actividades as $a) {
            echo "<tr><td style='$td'>".date('d/m/Y', strtotime($a['fecha']))."</td><td style='$td'>".substr($a['hora_inicio'],0,5)." - ".substr($a['hora_fin'],0,5)."</td><td style='$td'>{$a['titulo']}</td><td style='$td'>".strtoupper($a['tipo'])."</td><td style='$td'>{$a['resp_nombre']} {$a['resp_apellido']}</td><td style='$td'>{$a['descripcion']}</td></tr>";
        }

        echo "<tr><td colspan='6'></td></tr>";

        // SECCIÓN CUMPLEAÑOS
        echo "<tr><td colspan='6' style='background-color:#eee; font-weight:bold;'>🎂 CUMPLEAÑOS DEL MES</td></tr>";
        echo "<tr><th colspan='2' style='$th; background-color:#ff6b6b;'>Día</th><th colspan='4' style='$th; background-color:#ff6b6b;'>Hermano/a</th></tr>";
        
        if(empty($cumpleanos)) { echo "<tr><td colspan='6' align='center'>Sin cumpleaños</td></tr>"; }
        foreach($cumpleanos as $c) {
            echo "<tr><td colspan='2' style='$td' align='center'>{$c['dia']}</td><td colspan='4' style='$td'>{$c['nombre']} {$c['apellido']}</td></tr>";
        }

        echo "<tr><td colspan='6'></td></tr>";

        // SECCIÓN ORACIONES
        echo "<tr><td colspan='6' style='background-color:#eee; font-weight:bold;'>🙏 PETICIONES DE ORACIÓN</td></tr>";
        echo "<tr><th colspan='2' style='$th; background-color:#f59e0b;'>Fecha</th><th colspan='2' style='$th; background-color:#f59e0b;'>Solicitante</th><th colspan='2' style='$th; background-color:#f59e0b;'>Petición</th></tr>";
        
        if(empty($oraciones)) { echo "<tr><td colspan='6' align='center'>Sin peticiones</td></tr>"; }
        foreach($oraciones as $o) {
            echo "<tr><td colspan='2' style='$td'>".date('d/m/Y', strtotime($o['fecha']))."</td><td colspan='2' style='$td'>{$o['nombre']} {$o['apellido']}</td><td colspan='2' style='$td'>{$o['descripcion']}</td></tr>";
        }

        echo "</table>";
        exit; 
    }