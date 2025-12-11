<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../sesiones/session.php';
$sesionObj = new Session();
$usuarioData = $sesionObj->getSession();

require_once __DIR__ . '/../models/actividadesModel.php';

$accion = $_REQUEST['accion'] ?? null;
$obj = new ActividadesModel();
$idUsuario = $usuarioData['idusuario'] ?? null;

if (!$accion) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'No se especificó ninguna acción']);
    exit;
}

// CASO ESPECIAL: exportExcel NO devuelve JSON, devuelve archivo Excel
if ($accion === 'exportExcel') {
    try {
        if (!isset($_GET['year']) || !isset($_GET['month'])) {
            die('Error: Faltan parámetros año y mes');
        }
        
        $year = intval($_GET['year']);
        $month = intval($_GET['month']);
        
        // Verificar que existe el archivo exportador
        $exporterPath = __DIR__ . '/../exportar/exportarActividades.php';
        if (!file_exists($exporterPath)) {
            die('Error: No se encontró el archivo exportarActividades.php en ' . $exporterPath);
        }
        
        // Verificar que existe autoload de composer
        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($autoloadPath)) {
            die('Error: PhpSpreadsheet no está instalado. Ejecuta: composer require phpoffice/phpspreadsheet');
        }
        
        require_once $exporterPath;
        
        $exporter = new ExportarActividades();
        $exporter->generarResumenMensual($year, $month);
        exit;
        
    } catch (Exception $e) {
        die('Error al generar Excel: ' . $e->getMessage() . '<br>Línea: ' . $e->getLine() . '<br>Archivo: ' . $e->getFile());
    }
}

// Para el resto de acciones, devolver JSON
header('Content-Type: application/json; charset=utf-8');

switch ($accion) {
    case 'getResponsables':
        echo json_encode($obj->getResponsables());
        break;

    case 'insert':
        if (!$idUsuario) {
            echo json_encode(['error' => 'Sesión no válida']);
            exit;
        }
        
        $conflictos = $obj->checkTimeConflicts($_POST['fecha'], $_POST['hora_inicio'], $_POST['hora_fin']);
        
        if (count($conflictos) > 0 && !isset($_POST['force_save'])) {
            $detalles = array_map(function($c) {
                return $c['titulo'] . ' (' . $c['hora_inicio'] . ' - ' . $c['hora_fin'] . ')';
            }, $conflictos);
            
            echo json_encode([
                'conflict' => true,
                'message' => 'Ya existen actividades programadas en ese horario',
                'details' => $detalles
            ]);
            exit;
        }
        
        echo json_encode($obj->insert([
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'],
            'fecha' => $_POST['fecha'],
            'hora_inicio' => $_POST['hora_inicio'],
            'hora_fin' => $_POST['hora_fin'],
            'tipo' => $_POST['tipo'],
            'responsable' => $_POST['responsable'],
            'frecuencia' => $_POST['frecuencia'] ?? 'unica',
            'idusuario' => $idUsuario
        ]));
        break;

    case 'getCalendarEvents':
        $start = $_POST['start'] ?? null;
        $end = $_POST['end'] ?? null;
        
        if (!$start || !$end) {
            echo json_encode([]);
            exit;
        }
        
        $events = [];

        try {
            $actividades = $obj->getActivitiesByRange($start, $end);
            $ahora = date('Y-m-d H:i:s');
            
            foreach ($actividades as $act) {
                $fechaHoraFin = $act['fecha'] . ' ' . $act['hora_fin'];
                
                if ($fechaHoraFin < $ahora && $act['estado'] !== 'finalizada') {
                    $obj->updateEstado($act['idactividad'], 'finalizada');
                    $act['estado'] = 'finalizada';
                }
                
                $color = '#4b5563';
                if ($act['tipo'] == 'culto') $color = '#18c5a3';
                if ($act['tipo'] == 'reunion') $color = '#2b66b3';
                if ($act['tipo'] == 'ensayo') $color = '#6d28d9';
                
                if ($act['estado'] === 'finalizada') {
                    $color = '#6b7280';
                }
                
                $startISO = date('Y-m-d', strtotime($act['fecha'])) . 'T' . $act['hora_inicio'];
                $endISO = date('Y-m-d', strtotime($act['fecha'])) . 'T' . $act['hora_fin'];
                
                $events[] = [
                    'id' => 'act_' . $act['idactividad'],
                    'title' => $act['titulo'],
                    'start' => $startISO,
                    'end' => $endISO,
                    'backgroundColor' => $color,
                    'borderColor' => 'transparent',
                    'allDay' => false,
                    'extendedProps' => [
                        'tipo' => 'actividad',
                        'desc' => $act['descripcion'] ?? '',
                        'responsable' => trim(($act['resp_nombre'] ?? '') . ' ' . ($act['resp_apellido'] ?? '')),
                        'estado' => $act['estado']
                    ]
                ];
            }

            $oraciones = $obj->getApprovedPrayersByRange($start, $end);
            foreach ($oraciones as $oracion) {
                $events[] = [
                    'id' => 'ora_' . $oracion['idoracion'],
                    'title' => '🙏 Oración',
                    'start' => $oracion['fecha'],
                    'backgroundColor' => '#f59e0b',
                    'borderColor' => 'transparent',
                    'allDay' => true,
                    'extendedProps' => [
                        'tipo' => 'oracion',
                        'solicitante' => $oracion['solicitante'] ?? 'Anónimo',
                        'desc' => $oracion['descripcion'] ?? ''
                    ]
                ];
            }

            $startMonth = (int)date('m', strtotime($start));
            $endMonth = (int)date('m', strtotime($end));
            $years = [date('Y', strtotime($start)), date('Y', strtotime($end))];
            $years = array_unique($years);
            
            $mesesAConsultar = range($startMonth, $endMonth);
            if ($endMonth < $startMonth) {
                $mesesAConsultar = [12, 1];
            }

            foreach ($mesesAConsultar as $mes) {
                $cumples = $obj->getBirthdaysByMonth($mes);
                foreach ($cumples as $c) {
                    foreach ($years as $year) {
                        $fechaCumple = $year . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT) . '-' . date('d', strtotime($c['fecha_nacimiento']));
                        if ($fechaCumple >= $start && $fechaCumple <= $end) {
                            $events[] = [
                                'id' => 'bday_' . $c['idmiembro'] . '_' . $year,
                                'title' => '🎂 ' . $c['nombre'] . ' ' . $c['apellido'],
                                'start' => $fechaCumple,
                                'backgroundColor' => '#ec4899',
                                'borderColor' => 'transparent',
                                'allDay' => true,
                                'extendedProps' => [
                                    'tipo' => 'cumpleanos',
                                    'nombre' => $c['nombre'] . ' ' . $c['apellido']
                                ]
                            ];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }

        echo json_encode($events);
        break;

    case 'getMonthlySummary':
        $year = $_POST['year'] ?? date('Y');
        $month = $_POST['month'] ?? date('m');
        echo json_encode($obj->getMonthlySummary($year, $month));
        break;
    
    case 'getAttendanceData':
        $idactividad = $_POST['idactividad'] ?? null;
        if (!$idactividad) {
            echo json_encode(['error' => 'ID de actividad no válido']);
            exit;
        }
        
        $miembros = $obj->getAttendanceByActivity($idactividad);
        echo json_encode(['success' => true, 'miembros' => $miembros]);
        break;

    case 'saveAttendance':
        $idactividad = $_POST['idactividad'] ?? null;
        $asistentes = json_decode($_POST['asistentes'] ?? '[]', true);
        
        if (!$idactividad) {
            echo json_encode(['error' => 'ID de actividad no válido']);
            exit;
        }
        
        echo json_encode($obj->saveAttendance($idactividad, $asistentes));
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>