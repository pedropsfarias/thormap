<?php
/***********************************************************
	Esse arquivo contém todas as funções essenciais para o
    funcionamento do ThorMap.
    
                          Licença: GNU General Public License
                                        Pedro Farias Jul 2016
************************************************************/


function MontarSQL($caminho)
{
     $nome_DF     = "";
     $nome_grupo  = "";
     $nome_camada = "";

     $num_linhas =  count(file($arquivo));

     //Dados de conexão com Banco de Dados
     // Linhas 2,3,4,5
     $db = InfoDBfromTM($caminho, "String");
     
     $fp = fopen($caminho, 'r');
     $fp = AvancaFP($fp, 5);
     // Linha 6
     trim(fgets($fp));

     //SRID de Destino
     // Linha 7
     $SRID = trim(fgets($fp));
     // Linha 8
     trim(fgets($fp));
     
     //Num DataFrames
     // Linha 9
     $num_DF = trim(fgets($fp));
     
     $dataFrame = "";
     $k = 0;
     while($k < $num_DF)
     {
         // Nome do DataFrame
         trim(fgets($fp));
         $nome_DF = trim(fgets($fp));
         trim(fgets($fp));
         
         // Num de grupos
         $num_grupos = trim(fgets($fp));
         
         $grupo = "";
         $l = 0;
         while ($l < $num_grupos) 
         {
             // Nome do Grupo
             trim(fgets($fp));
             $nome_grupo = trim(fgets($fp));
             trim(fgets($fp));
            
             // Num Camadas
             $num_camadas = trim(fgets($fp));
            
             $camada = "";
             $m = 0;
             while ($m < $num_camadas) 
             {
                //Nome Camada
                trim(fgets($fp));
                $nome_camada = trim(fgets($fp));
                trim(fgets($fp));
                
                $tabela  = trim(fgets($fp));
                $colunas = trim(fgets($fp));
                $cond    = trim(fgets($fp));
                $c       = trim(fgets($fp));


                $consultas = json_decode($colunas, true);
                
                if (trim($cond) != "") {
                    
                    $cond = "WHERE ".$cond;
                }
                
             
                if(array_key_exists('geom', $consultas))
                {
                    $Dados = array("Camada" => $nome_camada,
                                   "geom"   => "SELECT ST_AsGeoJSON(ST_Transform(".$consultas["geom"].", ".$SRID.")) FROM ".$tabela." ".$cond,
                                   "Estilo" => $c);
                                   
                  
                                   
                    $chave = array_keys($consultas);              
                    for ($i=0; $i < count($consultas); $i++) 
                    {
                        if ($chave[$i] != "geom") 
                        {
                            $Dados[$chave[$i]] = "SELECT ".$consultas[$chave[$i]]." FROM ".$tabela." ".$cond;
                        } 
                    }
                    
                    //print_r($Dados);
                }
                else
                {
                    echo "Erro de formatação: A entrada 'geom'  de \"".$nome_camada."\" não está presente!";
                    exit;
                }


                $Dados = array('SQL' => $Dados, 'CHAVE' => $chave );
                $camada[0] = $nome_grupo;
                $camada[$m+1] = $Dados;
                
                $m++;
                

             }
             $grupo[0] = $nome_DF;
             $grupo[$l+1] = $camada;
             $l++;
         }
         $dataFrame[$k] = $grupo;
         $k++;
     }
     
     $SQLTM = array("db" => $db, "data" => $dataFrame );

     if (trim(fgets($fp)) == "#!") 
     {
         fclose($fp);
         return $SQLTM;
     }
     else
     {
         fclose($fp);
         return false;

     }
     
     
              
}
//----------------------------------------------------------- 
function InfoDBfromTM($caminho, $tipo)
{
    $fp = fopen($caminho, 'r');
    $fp = AvancaFP($fp, 1);
    
    if($tipo == "String")
    {
        $arr = trim(fgets($fp))." ".trim(fgets($fp))." ".trim(fgets($fp))." ".trim(fgets($fp));  
        fclose($fp);            
        return $arr; 
    }
    elseif ($tipo == "Array") 
    {
        $arr = array("host"     => trim(fgets($fp)),
                     "dbname"   => trim(fgets($fp)),
                     "user"     => trim(fgets($fp)),
                     "password" => trim(fgets($fp))); 
         
        fclose($fp);            
        return $arr;
    }
    
     
}
//----------------------------------------------------------- 
function AbrirTM($caminho)
{
    /* Abre arquivo .ThorMap */
    $resultado = "";
    
    $fp   = fopen($caminho, 'r');
    $tipo = trim(fgets($fp));
    $fp   = fclose($fp);
   
   
    switch ($tipo) 
    {
        case "SQL":
            $resultado = MontarSQL($caminho);
            break;

    }
    
    return $resultado;
    
}
//-----------------------------------------------------------    
function Db2Json($data)
{
    $e2 = "";
    $e3 = "";
    $e4 = "";
    $n  = "";
    $s  = ",";  
       

    //DataFrame
    $db        = $data["db"];
    $dataframe = $data["data"];
    $cdf = count($dataframe);
    
    // Inicio
    $Json  = "[";

    for ($i = 0; $i < $cdf; $i++) 
	{
        $Json .= "{\"type\" : \"DataFrame\"$s$n";
        $Json .= "  \"features\" : [$n";
        
        $df = $dataframe[$i];
        $num_grupos = count($df);
        
        
        for ($j = 1; $j < $num_grupos; $j++) 
	    {
            $grupo = $df[$j];
            $Json .= $e3."{ \"type\" : \"Group\"$s$n";
            $Json .= "$e3 \"properties\" : { \"name\" : \"".$grupo[0]."\" }$s$n";
            $Json .= "$e3 \"features\" : [$n";
            
            
            $num_camadas = count($grupo);
            for ($k = 1; $k < $num_camadas; $k++) 
	        {
               
                $camada = $grupo[$k];
                $Json .= $e4."{ $n";
                $Json .= $e4." \"type\" : \"FeatureCollection\"$s$n";
                $Json .= $e4." \"features\" : ".CriarCamadaSQL($camada, $db).$s.$n;
                $Json .= $e4." \"properties\" : { \"name\" : \"".$camada["SQL"]["Camada"]."\", \"properties\" : ".$camada["SQL"]["Estilo"]." }$n";
               
                
                if ($num_camadas-1 == $k) 
                {
                    $Json .= $e4."}$n";
                } 
                else
                {
                    $Json .= $e4."}, $n";

                }
                
                
            }
            
            if ($num_grupos-1 == $j) 
            {
                $Json .= $e3."] } $n";
            } 
            else
            {
                $Json .= $e3."] }, $n";

            }
        }
        
        
        if ($cdf-1 == $i) 
        {
            $Json .= $e2."]}";
        } 
        else
        {
            $Json .= $e2."]}, $n";

        }
    }
    $Json .= "]";
    return $Json;
	
}
//----------------------------------------------------------- 
function CriarCamadaSQL($Dados, $dbconn)
{
    
    $consulta  = $Dados["SQL"];
    $chave     = $Dados["CHAVE"];
    $resultado = array();
    $iGeom     = -1;
    
    if(!$dbcon = pg_connect($dbconn)) die ("Erro ao conectar ao banco!".pg_last_error($dbcon));
                
    for ($i=0; $i < count($chave) ; $i++) 
    { 
        if ($chave[$i] == "geom") 
        {
           $iGeom = $i;
        }
        
        $query = $consulta[$chave[$i]];
		$result = pg_query($dbcon, $query);
        
        if (!$result) 
        {
            echo "Erro de consulta: \"geom\"";
            exit;
        }
			
			
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) 
	    {
    		$string = "";
    		foreach ($line as $col_value) 
    		{
        		$string = $string."$col_value";
    		}
    		$string = $string."";
    		$R[] = $string;
    		$string = "";
		}
		
        $resultado[] = $R;
       
        $R = array();	
		pg_free_result($result);
        

    }
  
     /* Montar estrutura de uma camada */
	$Camada = "[";
    
    

        
        $tmp = $resultado[0]; 
        $counta = sizeof($tmp);
        
        
  
        for ($i = 0; $i < $counta; $i++) 
        {
            $UmaLinha = "{ \"type\": \"Feature\", ";
            $UmaLinha = $UmaLinha."\"geometry\" : ".$resultado[$iGeom][$i].",";
            $UmaLinha = $UmaLinha."\"properties\" : {";
            $tamChave = sizeof($chave);
            for ($j=0; $j < $tamChave; $j++) 
            { 
                if ($chave[$j] != "geom") 
                {
                    $UmaLinha = $UmaLinha." "."\"".$chave[$j]."\" : \"".$resultado[$j][$i]."\"";
                    
                    if ($j != $tamChave-1) 
                    {
                        $UmaLinha = $UmaLinha.",";
                    }
                    else 
                    {
                        $UmaLinha = $UmaLinha."}";
                    }
                }  
            } 
            
            
            if ($i == $counta-1) 
            {
                
                $UmaLinha = $UmaLinha."}";
            }
            else
            {
                $UmaLinha = $UmaLinha."},";
            }
                    
            $Camada = $Camada.$UmaLinha;
    	
        
        
        
        }	
    
        

 
	return $Camada."]";

}
//----------------------------------------------------------- 
function JSONfromTM($caminho)
{
    $tt = AbrirTM($caminho);
    return Db2Json($tt);    
}
//----------------------------------------------------------- 
function AvancaFP($fp, $num)
{
    for ($i=0; $i < $num; $i++) { 
        fgets($fp);
    }
    
    return $fp;
}
?>
