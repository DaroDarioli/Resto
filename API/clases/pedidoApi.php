<?php

require_once 'AccesoDatos.php';
require_once 'pedido.php';

class pedidoApi extends pedido
{

    public function CargarPedido($request, $response,$args)
    {
        $vPedido = new pedido();
        $vector = $request->getParsedBody();

        $vPedido->id_comanda = $vector['id_comanda'];
        $vPedido->id_producto = $vector['id_producto'];
        $vPedido->estado_pedido = "1";

        if (isset($_POST['cantidad_producto']) && !empty($_POST['cantidad_producto']))
        {
            $vPedido->cantidad_producto = $vector['cantidad_producto'];
        }else{
            $vPedido->cantidad_producto = 100;
        }
        return $vPedido->Insertar();
    }

    public function TraerPendientes($request, $response, $args) 
    {
        $Pedidos = pedido::TraerTodosLosPedidosPendientes();
        $newResponse = $response->withJson($Pedidos, 200);
        return $newResponse;
    }

    public function TraerPedidos($request, $response, $args) 
    {
        $Pedidos = pedido::TraerTodosLosPedidos();
        $newResponse = $response->withJson($Pedidos, 200);
        return $newResponse;
    }

    public function TraerTiempoFaltante($request, $response, $args) 
    {
        $vector = $request->getParams('id_comanda');
        $pPedido = $vector['id_comanda'];

        $Pedidos = pedido::TraerTiempoPedido($pPedido);

        if( 1 > (sizeof($Pedidos)))
        {
            $tiempo = -1;
            $resp["tiempoFaltante"] = $tiempo;
            return $response->withJson($resp);
        }

        $varHora = $Pedidos[0]['hora_estimada'];
        foreach($Pedidos as $mydata)
        {
            if($mydata['hora_estimada'] > $varHora){
                $varHora = $mydata['hora_estimada'];
            }

        }
        $hora = new DateTime('now',new DateTimeZone('America/Argentina/Buenos_Aires'));
        $horaAux = $hora->format('Y-m-d H:i:s');
        $horaActual = strtotime($horaAux);
        $horaComanda = strtotime($varHora);
        $faltante = $horaComanda - $horaActual;
        $tiempo = date("i:s",$faltante);
        $resp["tiempoFaltante"] = $tiempo;
        return $response->withJson($resp);
    }

    public function AgregarPedidoAComanda($request, $response, $args) 
    {
	$objDelaRespuesta= new stdclass();
        $pPedido = new pedido();
        $existe = false;
        $vector = $request->getParams('id_comanda,id_producto,cantidad_producto');
        $pPedido->id_comanda = $vector['id_comanda'];
        $pPedido->id_producto = $vector['id_producto'];
        $pPedido->cantidad_producto = $vector['cantidad_producto'];
        $pPedido->estado_pedido = 1;

        $pedidos = pedido::TraerPedidosPorComanda($pPedido->id_comanda);

        for ($x = 0; $x <= count($pedidos)-1; $x++)
        {

            $producto = $pedidos[$x]["id_producto"];

            if($producto == $pPedido->id_producto)
            {
                $existe = true;
                $pPedido->cantidad_producto = $pPedido->cantidad_producto  + $pedidos[$x]["cantidad_producto"];
               
                $cantidadModificados = $pPedido->ModificarPedido();

	    
	    	$objDelaRespuesta->cantidad=$cantidadModificados;

	   	if($cantidadModificados == 1){		
			$objDelaRespuesta->resultado="Se modifico un elemento!!!";
                }
	    	elseif($cantidadModificados > 1){
		       $objDelaRespuesta->resultado="Se modifico más de un elemento!!!";
                }
	    	elseif($cantidadModificados < 1){
 		       $objDelaRespuesta->resultado="No se modifico ningún elemento!!!";
                }
	
	    	$newResponse = $response->withJson($objDelaRespuesta, 200);	    
            }

        }


        if($existe == false)
        {
		$objDelaRespuesta = $pPedido->Insertar();
		$newResponse = $response->withJson($objDelaRespuesta, 200);
        }
       // $response->withJson($pPedido);
       // return $response;

	return $newResponse;

    }

	public function ModificaCantidad($request, $response, $args) {

        $unPedido = new pedido();

	$vector = $request->getParams('id_comanda,id_producto,cantidad');

        $cant = $vector['cantidad'];       
        $vComanda = $vector['id_comanda'];
        $vPedido = $vector['id_producto'];

        $var = pedido::TraerUnPedido($vComanda,$vPedido);

        if($var != null)
        {
            $unPedido = $var[0];
            $unPedido->cantidad_producto = $cant;
            $cantidadDeBorrados = $unPedido->ModificarPedido();

            $objDelaRespuesta= new stdclass();
            $objDelaRespuesta->cantidad=$cantidadDeBorrados;

            if($cantidadDeBorrados == 1)$objDelaRespuesta->resultado="Se modifico un elemento!!!";

            elseif($cantidadDeBorrados > 1) $objDelaRespuesta->resultado="Se modifico más de un elemento!!!";

            elseif($cantidadDeBorrados < 1) $objDelaRespuesta->resultado="No se modifico ningún elemento!!!";

            // $tarea = "Modifica pedido en comanda: ".$vComanda;
            // $id = logueo::ObtenerId($request, $response, $args);
            // logueo::InsertarTransaccion($id,$tarea);
            $newResponse = $response->withJson($objDelaRespuesta, 200);
            return $newResponse;
        }
        else
        {
            return "El no existe ningún pedido con esos valores";
        }        
    }




    public function TraerPedidosDeComanda($request, $response, $args) 
    {
        $vector = $request->getParams('id_mesa');
        $pMesa = $vector['id_mesa'];

        $vPedido = new pedido();
        $pedidosArray=array();
        $cantidadesArray=array();
        $var = comanda::TraerComandaMesa($pMesa);

//	return $response->withJson($var);	
        if($var != null)
        {
            $com = new comanda();
            $com = $var[0];
            $pedidos = pedido::TraerPedidosPorComandaNombres($com->id_comanda);
        }
        return $response->withJson($pedidos);
    }

    #used
    public function TraerPedidosComanda($request, $response, $args) 
    {
        $vector = $request->getParams('id_mesa');
        $pMesa = $vector['id_mesa'];
        $vPedido = new pedido();
        $pedidosArray=array();
        $cantidadesArray=array();

        $var = comanda::TraerComandaMesa($pMesa);
        if($var != null)
        {
            $com = new comanda();
            $com = $var[0];
            $pedidos = pedido::TraerPedidosPorComanda($com->id_comanda);

            $total = 0;

            for ($x = 0; $x <= count($pedidos)-1; $x++) 
            {
                $producto = $pedidos[$x]["id_producto"];
                $cantidad = $pedidos[$x]["cantidad_producto"];
                array_push($pedidosArray,$producto);
                array_push($cantidadesArray,$cantidad);
            }
            $total = $vPedido->TraerPrecios($pedidosArray,$cantidadesArray);
        }        
        return $response->withJson($total);
    }

    public function PedidosPorMozo($request, $response, $args) 
    {
        $vector = $request->getParams('id_mozo');
        $vId = $vector['id_mozo'];
        $Pedidos = pedido::TraerTodosLosPedidosPorIdMozo($vId);
        return $response->withJson($Pedidos, 200);
    }

    public function PedidosDemorados($request, $response, $args) 
    {
        $Pedidos = pedido::TraerPedidosDemorados();
        return $response->withJson($Pedidos, 200);
    }

    public function TraerTodosLosPendientesSector($request, $response, $args) 
    {
        $vector = $request->getParams('id_cocina');
        $vId = $vector['id_cocina'];

        $Pedidos = pedido::TraerTodosLosPedidosPendientesSector($vId);
        $newResponse = $response->withJson($Pedidos, 200);     
        return $newResponse;
    }

    public function TomarUnPedido($request, $response,$args)
    {
        $unPedido = new pedido();
        $vHora = new DateTime();
        $vector = $request->getParsedBody();

        $vector = $request->getParams('minutos_estimados', 'id_comanda', 'id_producto','id_empleado');
      //  $id = logueo::ObtenerId($request, $response, $args);

        $unPedido->id_comanda = $vector['id_comanda'];
        $unPedido->id_producto = $vector['id_producto'];
        $unPedido->id_empleado = $vector['id_empleado'];
        $unPedido->comienzo_preparacion = date_format($vHora,"Y/m/d H:i:s");

        $minutos = $vector['minutos_estimados'];
        $agregar = $minutos." minutes";

        $vHoraEstipulada = $vHora;
        date_add($vHoraEstipulada, date_interval_create_from_date_string($agregar));
        $unPedido->hora_estimada = date_format($vHoraEstipulada,"Y/m/d H:i:s");

        //return var_dump($unPedido);

        $resultado = $unPedido->TomarPedido();

     //   $tarea = "Se toma pedido en comanda: ".$vComanda;
      //  logueo::InsertarTransaccion($id,$tarea);

        $responseObj= new stdclass();
        $responseObj->resultado=$resultado;
        $responseObj->tarea="modificar";
        return $response->withJson($responseObj, 200);     
    }

    // public function FinalizarUnPedido($request, $response,$args)
    // {
    //     $unPedido = new pedido();
    //     $unPedidoAuxiliar = new pedido();

    //     $vHora = new DateTime();
    //     $responseObj= new stdclass();

    //     if ((!isset($_GET['id_comanda']) || empty($_GET['id_comanda'])) || ((!isset($_GET['id_producto'])) || empty($_GET['id_producto'])))
	// 	{

	// 		$obj->respuesta="Favor ingresar datos obligatorios";
	// 		$nueva=$response->withJson($obj, 401);
	// 		return $nueva;

	// 	}
	// 	else{

    //         $vector = $request->getParams('id_comanda', 'id_producto');
    //       //  $id = logueo::ObtenerId($request, $response, $args);
    //         $unPedido->id_comanda = $vector['id_comanda'];
    //         $unPedido->id_producto = $vector['id_producto'];
    //         //$unId =  $id;
    //         $unPedido->hora_listo = date_format($vHora,"Y/m/d H:i:s");

    //         // $var = pedido::TraerPedidoPendiente($unPedido->id_comanda,$unPedido->id_producto);
    //         // $unPedidoAuxiliar = $var[0];

    //         // return var_dump($unPedidoAuxiliar);

    //         $resultado = $unPedido->FinalizarPedido();
    //         $responseObj= new stdclass();
    //         $responseObj->resultado=$resultado;

    //         return $response->withJson($responseObj, 200);

    //         if($var != null)
    //         {
    //             if($unPedidoAuxiliar->id_empleado == $unId)
    //             {
    //                 $resultado = $unPedido->FinalizarPedido();
    //                 $responseObj= new stdclass();
    //                 $responseObj->resultado=$resultado;
    //                 $responseObj->tarea="Finaliza pedido en comanda: ".$unPedido->id_comanda;
    //                 logueo::InsertarTransaccion($id,$responseObj->tarea);
    //             }
    //             else
    //             {
    //                 $responseObj->resultado = False ;
    //                 $responseObj->MensajeError= "Id de empleado no corresponde con responsable del pedido";
    //             }

    //         }
    //         else
    //         {
    //             $responseObj->resultado = False ;
    //             $responseObj->MensajeError= "no existe dicho pedido como pendiente";


    //         }
    //         return $response->withJson($responseObj, 200);
    //     }
    // }

	 

    public function ModificaCantidadPedido($request, $response, $args) {

        $unPedido = new pedido();

        $cant = $args['cantidad'];
        $vector = $request->getParams('id_comanda','id_producto');
        $vComanda = $vector['id_comanda'];
        $vPedido = $vector['id_producto'];
        $var = pedido::TraerUnPedido($vComanda,$vPedido);

        if($var != null)
        {
            $unPedido = $var[0];
            $unPedido->cantidad_producto = $cant;
            $cantidadDeBorrados = $unPedido->ModificarPedido();

            $objDelaRespuesta= new stdclass();
            $objDelaRespuesta->cantidad=$cantidadDeBorrados;

            if($cantidadDeBorrados == 1)$objDelaRespuesta->resultado="Se modifico un elemento!!!";

            elseif($cantidadDeBorrados > 1) $objDelaRespuesta->resultado="Se modifico más de un elemento!!!";

            elseif($cantidadDeBorrados < 1) $objDelaRespuesta->resultado="No se modifico ningún elemento!!!";

            // $tarea = "Modifica pedido en comanda: ".$vComanda;
            // $id = logueo::ObtenerId($request, $response, $args);
            // logueo::InsertarTransaccion($id,$tarea);
            $newResponse = $response->withJson($objDelaRespuesta, 200);
            return $newResponse;
        }
        else
        {
            return "El no existe ningún pedido con esos valores";
        }        
    }

    public function BorrarUnPedido($request, $response, $args)
    {
        $unPedido = new pedido();
        $vector = $request->getParams('id_comanda','id_producto');
        $vComanda = $vector['id_comanda'];
        $vPedido = $vector['id_producto'];
        $var = pedido::TraerUnPedido($vComanda,$vPedido);

        if($var != null)
        {
            $cantidadDeBorrados = $unPedido->BorrarPedido($vComanda,$vPedido);
            $auxiliar = $var[0];
            $auxiliar->InsertarBorrado();
            $objDelaRespuesta= new stdclass();
            $objDelaRespuesta->cantidad=$cantidadDeBorrados;

            if($cantidadDeBorrados == 1)$objDelaRespuesta->resultado="Se borró un elemento!!!";

            elseif($cantidadDeBorrados > 1) $objDelaRespuesta->resultado="Se borró más de un elemento!!!";

            elseif($cantidadDeBorrados < 1) $objDelaRespuesta->resultado="No se borró ningún elemento!!!";

            $newResponse = $response->withJson($objDelaRespuesta, 200);
            return $newResponse;
        }
        else
        {
            return "El no existe ningún pedido con esos valores";
        }
    }
}

?>
