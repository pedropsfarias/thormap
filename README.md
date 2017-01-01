# thormap
Tool to get spatial data from a PostgreSQL database

**[pt-br]**  
ThorMap é uma ferramenta para obter dados espaciais no formato GeoJSON armazenados em um banco de dados PostgreSQL. A motivação do desenvolvimento dessa ferramenta foi a necessidade de se manipular dados espaciais em ambiente *web*. Assim, a ferramenta foi desenvolvida em PHP e o arquivo de saída, no formato GeoJSON, pode ser manipulado em *Javascript*.  
A ferramenta funciona a partir de um arquivo de entrada (ASCII) e gera uma *string* com o GeoJSON. 


## Arquivo de entrada
O arquivo de entrada segue o seguinte formato:
```
1. STRING DE CONTROLE
2. LOCAL DO SERVIDOR
3. NOME DO BANCO DE DADOS
4. USUÁRIO
5. SENHA
-------------------------
6. SRID DO GeoJSON
-------------------------
7. NÚMERO DE DATAFRAMES
****************************************************************************************************
8. NOME DO DATAFRAME
****************************************************************************************************
    9. NÚMERO DE GRUPOS
    ..................................................
    10. NOME DO GRUPO
    ..................................................
        11. NÚMERO DE FEIÇÕES
        -------------------------
        12. NOME DA FEIÇÃO
        -------------------------
            13. NOME TABELA
            14. STRING NO FORMATO JSON COM AS INFORMAÇÕES QUE DEVERÃO SER RECUPERADAS DO BANCO
            15. CLÁUSULA WHERE SQL
            16. STRING NO FORMATO JSON COM AS INFORMAÇÕES ADICIONAIS QUE DEVERÃO SER ADICIONADAS AO ARQUIVO DE SAÍDA
```
Onde (⚑ OBRIGATÓRIO):

###### 1. STRING DE CONTROLE ⚑  
Sempre deve ser "SQL".  


###### 2. LOCAL DO SERVIDOR ⚑  
String que indica o local do servidor. Sempre iniciada com "host".    
Ex.: `host=localhost`


###### 3. NOME DO BANCO DE DADOS ⚑  
String que indica o nome do banco de dados no PostgreSQL. Sempre iniciada com "dbname".       
Ex.: `dbname=teste`


###### 4. USUÁRIO ⚑  
String que indica o nome do usuário no PostgreSQL. Sempre iniciada com "user".    
Ex.: `user=pedro`


###### 5. SENHA ⚑    
String que indica a senha do usuário no PostgreSQL. Sempre iniciada com "password".     
Ex.: `password=12345`


###### 6. SRID DO GeoJSON  ⚑  
Número do Sistema de Referência (EPSG) do conjunto de coordenadas do arquivo de saída (GeoJSON). Esse id pode ser obtido em www.spatialreference.org.   
Ex., para WGS84: `4326`


###### 7. NÚMERO DE DATAFRAMES ⚑  
Um data frame é um conjunto de dados que compõe um mapa. Análogo ao data frame do ArcGIS. Esse número indica a quentidade de data frames.    
Ex.: `1`


###### 8. NOME DO DATAFRAME  
Nome do data frame. Análogo ao do ArcGIS.  
Ex.: `Uso do Solo`


###### 9. NÚMERO DE GRUPOS ⚑  
Número de grupos do data frame.   
Ex.: `1`


###### 10. NOME DO GRUPO    
Nome do grupo. Análogo ao do ArcGIS/QGIS.  
Ex.: `Atividade Humana`


###### 11. NÚMERO DE FEIÇÕES ⚑  
Número de feições do grupo. 
Ex.: `1`


###### 12. NOME DA FEIÇÃO  
Nome da feição. Análogo ao do ArcGIS/QGIS.  
Ex.: `Agricultura`


###### 13. NOME TABELA ⚑
Nome da tabela onde estão os dados. Pode ser acompanhada schema do banco de dados.   
Ex.: `public.agricultura` ou `agricultura`


###### 14. STRING COM AS INFORMAÇÕES QUE DEVERÃO SER RECUPERADAS DO BANCO ⚑  
Nome das colunas de onde devarão ser retiradas as informações e como deverão ser nomeadas no arquivo de saída. Formato JSON.   
*OBRIGATÓRIA A ENTRADA "GEOM" COM A COLUNA DA TABELA QUE CONTÉM AS GEOMETRIAS*     
Ex., a coluna "geometry" na tabela deverá ser nomeada de "geom": `{ "geom" : "geometry" }`  
Ex., a coluna "escala" deverá permanecer com o mesmo nome: `{ "escala" : "escala" }`  
Ex., as colunas "geom", "nome" e "id" deverão ser recuperadas: `{ "geom" : "geom", "nome" : "nome", "id" : "id" }`  


###### 15. CLÁUSULA WHERE SQL  
String para a inserção de uma cláusula WHERE. Caso não exista deixar em branco.  
Ex.: `ano = 2017`


###### 16. STRING COM AS INFORMAÇÕES QUE DEVERÃO SER ADICIONADAS AO ARQUIVO DE SAÍDA  
String no formato JSON que serão adicionadas diretamente às "properties" da feição no arquivo de saída.  
Ex.: `{color: "#809848", weight: 0.26, opacity: 1, fillColor: "#dde0d1", fillOpacity: 1}`  


## Exemplo (arquivo.thormap):
```
SQL
host=localhost
dbname=teste
user=postgres
password=postgres
-------------------------
4326
-------------------------
1
****************************************************************************************************
Mapa de Uso do Solo
****************************************************************************************************
1
    ..................................................
    Atividade Humana
    ..................................................
    1
        -------------------------
        Agricultura
        -------------------------
            public.agricultura
            {"geom" : "geometria", "tipo":"uso_principal"}
            ano = 2017
            {color: "#809848", weight: 0.26, opacity: 1, fillColor: "#dde0d1", fillOpacity: 1}
```





## Arquivo de saída
A arquitetura do GeoJSON de saída é: 
```
[{
	"type": "DataFrame",
	"features": [{
		"type": "Group",
		"properties": {
			"name": "fundo"
		},
		"features": [{
			"type": "FeatureCollection",
			"features": [{
				"type": "Feature",
				"geometry": {
					"type": "Polygon",
					"coordinates": [
						[
							[-49.2369968004462, -25.4576610345322],
							[-49.2388461941933, -25.4549679072122],
							[-49.2370172131807, -25.4538119008722],
							.
							.
							.
							[-49.2369968004462, -25.4576610345322]
						]
					]
				},
				"properties": {
					"andar": "único"
				}
			}],
			"properties": {
				"name": "campus",
				"properties": {
					color: "#809848",
					weight: 0.26,
					opacity: 1,
					fillColor: "#dde0d1",
					fillOpacity: 1
				}
			}
		}]
	}]
}]
```

## Funcionamento
Para gerar um GeoJSON com dados espacias é necessário utilizar a seguinte rotina PHP:
```
<?php 
    include_once 'ThorMap.php';
    $meuJson = JSONfromTM('arquivo.thormap');
?>
```
