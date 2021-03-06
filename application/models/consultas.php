<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Consultas extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function listDatoSeguimiento($tra_id, $fecha, $cuenta){
        $statement = Doctrine_Manager::getInstance()->connection();
        $query="select 
                    nro_tramite, 
                    inicio,
                    termino, 
                    estado, 
                    tarea_id, 
                    tarea_nombre, 
                    usuario, 
                    inicio_filtro,
                    etapa_id
                 from (
                       select 
                       t.id as nro_tramite, 
                       (CASE WHEN LENGTH(t.id) = 6 THEN t.id - CONCAT(SUBSTRING('$fecha',3,4),'0000') ELSE t.id  END ) AS filtro, 
                       DATE_FORMAT(e.created_at,'%d-%m-%Y %H:%i:%s') as inicio, 
                       DATE_FORMAT(e.ended_at,'%d-%m-%Y %H:%i:%s') as termino, 
                       case when e.pendiente = 0 then 'Completado' else 'Pendiente' end as estado, 
                       ta.id as tarea_id, 
                       ta.nombre as tarea_nombre, 
                       concat(u.nombres,' ',u.apellido_paterno) as usuario,
                       DATE_FORMAT(t.created_at,'%Y') as inicio_filtro,
                       e.id as etapa_id
                    from etapa e,tarea ta,tramite t, usuario u, proceso p, cuenta c
                    where e.tarea_id=ta.id 
                      and e.tramite_id= t.id 
                      and e.usuario_id=u.id    
                      and t.proceso_id=p.id
                      and p.cuenta_id=c.id
                      and c.id=$cuenta->id                    
                    ) d
                where nro_tramite=$tra_id and inicio_filtro=DATE_FORMAT(STR_TO_DATE('$fecha','%Y'),'%Y')
                order by 4,2 asc";
        $results = $statement->execute($query);
        return $results->fetchAll();    
    }

    public function detalleEtapa($id_etapa){
      $statement = Doctrine_Manager::getInstance()->connection();
      $query="select 
                DATE_FORMAT(e.created_at,'%d-%m-%Y %H:%i:%s') as inicio, 
                DATE_FORMAT(e.ended_at,'%d-%m-%Y %H:%i:%s') as termino, 
                case when e.pendiente = 0 then 'Completado' else 'Pendiente' end as estado, 
                ta.id as tarea_id, 
                ta.nombre as tarea_nombre, 
                concat(u.nombres,' ',u.apellido_paterno) as usuario
                from etapa e,tarea ta, usuario u
                where e.tarea_id=ta.id
                    and e.usuario_id=u.id
                    and e.id=$id_etapa;
                order by 4,2 asc";
       $results = $statement->execute($query);
       return $results->fetchAll();    
   }
}