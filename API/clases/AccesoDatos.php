<?php
class AccesoDatos
{
    private static $ObjetoAccesoDatos;
    private $objetoPDO;
 
    private function __construct()
    {
        try { 
            $this->objetoPDO = new PDO('mysql:host=localhost;dbname=db_angular;charset=utf8', 'root', '', array(PDO::ATTR_EMULATE_PREPARES => false,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $this->objetoPDO->exec("SET CHARACTER SET utf8");
            } 
        catch (PDOException $e) { 
            print "Error!: " . $e->getMessage(); 
            die();
        }
    }


 
    public function RetornarConsulta($sql)
    { 
        return $this->objetoPDO->prepare($sql); 
    }
     public function RetornarUltimoIdInsertado()
    { 
        return $this->objetoPDO->lastInsertId(); 
    }
 
    public static function dameUnObjetoAcceso()
    { 
        if (!isset(self::$ObjetoAccesoDatos)) {          
            self::$ObjetoAccesoDatos = new AccesoDatos(); 
        } 
        return self::$ObjetoAccesoDatos;        
    }

    public function Borrados()
    {
        return mysql_affected_rows();
    }
    
 
 
     // Evita que el objeto se pueda clonar
    public function __clone()
    { 
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR); 
    }

    //Armado de consultas

    public static function ConsultaDatosAsociados($consulta)
    {
        $objetoAccesoDato = self::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->RetornarConsulta($consulta);
        $consulta->execute();      
        $consulta->setFetchMode(PDO::FETCH_ASSOC);
        return $consulta->fetchAll();
    }

    public static function ConsultaClase($consulta,$clase)
    {
        $objetoAccesoDato = self::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta($consulta);
        $consulta->execute();      
        $consulta->setFetchMode(PDO::FETCH_CLASS,$clase); 
        return $consulta->fetchAll();
    }

    //fin armado de consultas
}
?>
