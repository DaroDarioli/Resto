<?php

require_once 'AccesoDatos.php';



class mesa
{
    public $id_mesa;
    public $id_sector;
    public $id_estado_mesa;

    public function __construct() {}

        public function Insertar()
        {                      
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
            $consulta =$objetoAccesoDato->RetornarConsulta("INSERT into mesas (id_mesa,id_sector,id_estado_mesa)values(:id_mesa,:id_sector,:id_estado_mesa)");
            $consulta->bindValue(':id_mesa', $this->id_mesa, PDO::PARAM_STR);
            $consulta->bindValue(':id_sector', $this->id_sector, PDO::PARAM_STR);
            $consulta->bindValue(':id_estado_mesa', $this->id_estado_mesa, PDO::PARAM_STR);
            $consulta->execute();		

            return $objetoAccesoDato->RetornarUltimoIdInsertado();
        }

        public static function TraerTodos()
        {
            $consulta = "SELECT * FROM `mesas`";
            return AccesoDatos::ConsultaClase($consulta, "mesa");
        }
        
        public static function TraerUno($vId)
        {
            $consulta = "SELECT * FROM `mesas` WHERE  `id_mesa` = '$vId'";
            return AccesoDatos::ConsultaClase($consulta, "mesa");
        }

        public function Modificar()
        {
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
            $consulta =$objetoAccesoDato->RetornarConsulta("
                update mesas
                set id_sector ='$this->id_sector',
                id_estado_mesa ='$this->id_estado_mesa'
                WHERE id_mesa ='$this->id_mesa'");
            return $consulta->execute();
    
        }

        public function Cerrar()
        {
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
            $consulta =$objetoAccesoDato->RetornarConsulta("
                update mesas
                id_estado_mesa = 4
                WHERE id_mesa ='$this->id_mesa'");
            return $consulta->execute();
    
        }

        public function Borrar()
        {
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
            $consulta =$objetoAccesoDato->RetornarConsulta("
            delete 
            from mesas 				
            WHERE id_mesa =:id_mesa");	
            $consulta->bindValue(':id_mesa',$this->id_mesa, PDO::PARAM_INT);		
            $consulta->execute();
            return $consulta->rowCount();
        }
}

?>