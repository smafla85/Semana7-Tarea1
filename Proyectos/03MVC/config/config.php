<?php
class ClaseConectar
{
    public $conexion;
    protected $db;
    private $host = "127.0.0.1";
    private $usuario = "root";
    private $pass = "root";
    private $base = "sexto";
    private $puerto = "3307";
    public function ProcedimientoParaConectar()
    {
        $this->conexion = mysqli_connect($this->host, $this->usuario, $this->pass, $this->base, $this->puerto);
        mysqli_query($this->conexion, "SET NAMES 'utf8'");
        if ($this->conexion->connect_error) {
            die("Error al conectar con el servidor: " . $this->conexion->connect_error);
        }
        $this->db = $this->conexion;
        if ($this->db == false) die("Error al conectar con la base de datos: " . $this->conexion->connect_error);

        return $this->conexion;
    }
}
