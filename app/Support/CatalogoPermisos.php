<?php

namespace App\Support;

/**
 * Catálogo de los permisos que se asignan desde Usuarios: módulos, dependencias y plantillas de rol.
 * Es la única fuente: la pantalla de permisos, el guardado y los tests salen de acá.
 */
final class CatalogoPermisos
{
    /**
     * Permisos agrupados por módulo. 'requiere' = permiso que hace falta para que este sirva de algo
     * (editar sin ver no deja entrar a la pantalla; los de RRHH se apoyan en "ver personal").
     *
     * Cada módulo se muestra como una fila con un selector de nivel ('niveles': de menor a mayor, cada uno incluye
     * al anterior) y, si tiene, interruptores sueltos ('extras': permisos que se apoyan en el primer nivel).
     *
     * @return list<array{clave: string, titulo: string, icono: string, niveles: list<array{clave: string, titulo: string}>, extras: list<string>, permisos: list<array{clave: string, titulo: string, descripcion: string, requiere: ?string}>}>
     */
    public static function modulos(): array
    {
        return [
            [
                'clave' => 'expedientes',
                'titulo' => 'Expedientes',
                'icono' => 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z',
                'niveles' => [['clave' => 'expediente_ver', 'titulo' => 'Ver'], ['clave' => 'expediente_editar', 'titulo' => 'Editar']],
                'extras' => [],
                'permisos' => [
                    ['clave' => 'expediente_ver', 'titulo' => 'Ver expedientes', 'descripcion' => 'Acceso de lectura a expedientes.', 'requiere' => null],
                    ['clave' => 'expediente_editar', 'titulo' => 'Editar expedientes', 'descripcion' => 'Crear, modificar y eliminar expedientes.', 'requiere' => 'expediente_ver'],
                ],
            ],
            [
                'clave' => 'resoluciones',
                'titulo' => 'Resoluciones',
                'icono' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'niveles' => [['clave' => 'resolucion_ver', 'titulo' => 'Ver'], ['clave' => 'resolucion_editar', 'titulo' => 'Editar']],
                'extras' => [],
                'permisos' => [
                    ['clave' => 'resolucion_ver', 'titulo' => 'Ver resoluciones', 'descripcion' => 'Acceso de lectura a resoluciones.', 'requiere' => null],
                    ['clave' => 'resolucion_editar', 'titulo' => 'Editar resoluciones', 'descripcion' => 'Crear, modificar y eliminar resoluciones.', 'requiere' => 'resolucion_ver'],
                ],
            ],
            [
                'clave' => 'usuarios',
                'titulo' => 'Usuarios y oficinas',
                'icono' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87',
                'niveles' => [['clave' => 'lista_usuario_ver', 'titulo' => 'Ver'], ['clave' => 'lista_usuario_editar', 'titulo' => 'Editar']],
                'extras' => [],
                'permisos' => [
                    ['clave' => 'lista_usuario_ver', 'titulo' => 'Ver lista de usuarios', 'descripcion' => 'Acceso de lectura a usuarios.', 'requiere' => null],
                    ['clave' => 'lista_usuario_editar', 'titulo' => 'Editar lista de usuarios', 'descripcion' => 'Crear usuarios, asignar oficinas y permisos.', 'requiere' => 'lista_usuario_ver'],
                ],
            ],
            [
                'clave' => 'administracion',
                'titulo' => 'Administración',
                'icono' => 'M12 3 4 6v5c0 5 3.4 8.4 8 10 4.6-1.6 8-5 8-10V6l-8-3Z',
                'niveles' => [['clave' => 'admin_ver', 'titulo' => 'Sí']],
                'extras' => [],
                'permisos' => [
                    ['clave' => 'admin_ver', 'titulo' => 'Panel de administración', 'descripcion' => 'Auditoría, métricas, accesos y estado del sistema de toda la institución.', 'requiere' => null],
                ],
            ],
            [
                'clave' => 'rrhh',
                'titulo' => 'Recursos Humanos',
                'icono' => 'M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                'niveles' => [['clave' => 'rrhh_ver', 'titulo' => 'Ver']],
                'extras' => ['rrhh_sensibles', 'rrhh_horarios', 'rrhh_confirmar_pedidos'],
                'permisos' => [
                    ['clave' => 'rrhh_ver', 'titulo' => 'Ver personal', 'descripcion' => 'Legajos (datos personales), asistencia, licencias y reportes de todo el personal.', 'requiere' => null],
                    ['clave' => 'rrhh_sensibles', 'titulo' => 'Datos sensibles', 'descripcion' => 'Salud, discapacidad, datos judiciales y cuenta bancaria. Solo jefatura de RRHH.', 'requiere' => 'rrhh_ver'],
                    ['clave' => 'rrhh_horarios', 'titulo' => 'Horarios especiales', 'descripcion' => 'Cargar y finalizar horarios especiales (ej. 8 a 13). Solo jefatura de RRHH.', 'requiere' => 'rrhh_ver'],
                    ['clave' => 'rrhh_confirmar_pedidos', 'titulo' => 'Confirmar pedidos de licencia', 'descripcion' => 'Confirmar o rechazar los pedidos que aprobó el jefe y elegir qué licencias se pueden pedir.', 'requiere' => 'rrhh_ver'],
                ],
            ],
        ];
    }

    /**
     * Plantillas de rol. Al aplicar una, reemplaza los permisos de los módulos que toca y deja el resto como está.
     *
     * @return array<string, array{nombre: string, descripcion: string, permisos: list<string>}>
     */
    public static function roles(): array
    {
        return [
            'consulta' => [
                'nombre' => 'Consulta',
                'descripcion' => 'Ve expedientes y resoluciones, sin editar.',
                'permisos' => ['expediente_ver', 'resolucion_ver'],
            ],
            'operador' => [
                'nombre' => 'Operador de oficina',
                'descripcion' => 'Carga y edita expedientes; ve resoluciones.',
                'permisos' => ['expediente_ver', 'expediente_editar', 'resolucion_ver'],
            ],
            'redactor' => [
                'nombre' => 'Redactor de resoluciones',
                'descripcion' => 'Ve expedientes y crea o edita resoluciones.',
                'permisos' => ['expediente_ver', 'resolucion_ver', 'resolucion_editar'],
            ],
            'admin_usuarios' => [
                'nombre' => 'Administrador de usuarios',
                'descripcion' => 'Gestiona usuarios, oficinas y permisos; ve expedientes y resoluciones.',
                'permisos' => ['lista_usuario_ver', 'lista_usuario_editar', 'expediente_ver', 'resolucion_ver'],
            ],
            'rrhh_personal' => [
                'nombre' => 'Personal de RRHH',
                'descripcion' => 'Ve al personal y confirma pedidos de licencia, sin datos sensibles.',
                'permisos' => ['rrhh_ver', 'rrhh_confirmar_pedidos'],
            ],
            'rrhh_jefatura' => [
                'nombre' => 'Jefatura de RRHH',
                'descripcion' => 'Todo RRHH: datos sensibles, horarios especiales y confirmación de pedidos.',
                'permisos' => ['rrhh_ver', 'rrhh_sensibles', 'rrhh_horarios', 'rrhh_confirmar_pedidos'],
            ],
        ];
    }

    /**
     * Nivel de acceso de cada módulo para un conjunto de permisos (0 = sin acceso), para el resumen de la lista de usuarios.
     *
     * @param  list<string>  $activos
     * @return list<array{clave: string, titulo: string, icono: string, nivel: int, etiqueta: string, extras: int}>
     */
    public static function resumen(array $activos): array
    {
        $activos = array_flip($activos);

        return array_map(function (array $m) use ($activos) {
            $nivel = 0;
            foreach ($m['niveles'] as $i => $n) {
                if (isset($activos[$n['clave']])) {
                    $nivel = $i + 1;
                }
            }

            return [
                'clave' => $m['clave'],
                'titulo' => $m['titulo'],
                'icono' => $m['icono'],
                'nivel' => $nivel,
                'etiqueta' => $nivel > 0 ? $m['niveles'][$nivel - 1]['titulo'] : 'Sin acceso',
                'extras' => count(array_filter($m['extras'], fn (string $c) => isset($activos[$c]))),
            ];
        }, self::modulos());
    }

    /** @return list<string> */
    public static function claves(): array
    {
        return collect(self::modulos())->flatMap(fn (array $m) => array_column($m['permisos'], 'clave'))->all();
    }

    /**
     * Dependencias: permiso => permiso que necesita.
     *
     * @return array<string, string>
     */
    public static function dependencias(): array
    {
        $dependencias = [];
        foreach (self::modulos() as $modulo) {
            foreach ($modulo['permisos'] as $permiso) {
                if ($permiso['requiere'] !== null) {
                    $dependencias[$permiso['clave']] = $permiso['requiere'];
                }
            }
        }

        return $dependencias;
    }

    /**
     * Cierra un conjunto de permisos con sus dependencias: quien tiene "editar" tiene también "ver".
     * Ignora claves que no estén en el catálogo.
     *
     * @param  iterable<string>  $activos
     * @return list<string>
     */
    public static function normalizar(iterable $activos): array
    {
        $validas = array_flip(self::claves());
        $dependencias = self::dependencias();
        $resultado = [];

        foreach ($activos as $clave) {
            while ($clave !== null && isset($validas[$clave]) && ! isset($resultado[$clave])) {
                $resultado[$clave] = true;
                $clave = $dependencias[$clave] ?? null;
            }
        }

        return array_values(array_filter(self::claves(), fn (string $c) => isset($resultado[$c])));
    }

    /** Lo que necesita la pantalla en el navegador. */
    public static function paraPantalla(): array
    {
        return [
            'modulos' => self::modulos(),
            'dependencias' => self::dependencias(),
            'roles' => self::roles(),
        ];
    }
}
