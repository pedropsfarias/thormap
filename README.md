# thormap
Tool to get spatial data from a PostgreSQL database.

**[pt-br]**  
ThorMapPHP é uma biblioteca PHP para a criação de base de dados para representar ambientes indoor, junto com simbologia, manipuláveis em Javascript (ver thormap.js). Os dados devem estar armazenados em um banco de dados PostreSQL e a simbologia em arquivos \*.qml. O processo utilizado é: **modelo inicial >> processos intermediários >> thorJSON**.

## Modelo inicial:
O modelo inicial é um JSON que contém as informações necessárias para elaborar a base de dados.
```
//modelo.json
{
    "fonte": {
        "tipo": "postgresql",
        "nomeBanco": "dbname",
        "senha": "passwd",
        "usuario": "user",
        "maquina": "host",
        "SRID": 4326,
        "saida": "ucm.tm"
    },
    "mapas": [
        {
            "nomeMapa": "Planta Baixa",
            "colunaGrupo": "andar",
            "valoresGrupo": [0, 1, 2, 3, 4, 5],
            "tabelas": [
                {
                    "nomeTabela": "public.sala",
                    "colunas": [
                        "geom",
                        "nome_sala",
                        "sigla_sala",
                        "cod_sala",
                        "andar"
                    ],
                    "estilo": "sala.qml"
                },
                {
                    "nomeTabela": "public.corredor",
                    "colunas": [
                        "geom",
                        "andar"
                    ],
                    "estilo": "corredor.qml"
                }
            ]
        },
        {
            "nomeMapa": "Esquematico",
            "colunaGrupo": "andar",
            "valoresGrupo": [0, 1, 2, 3, 4, 5],
            "tabelas": [
                {
                    "nomeTabela": "public.transicao",
                    "colunas": [
                        "geom",
                        "andar"
                    ],
                    "estilo": "transicao.qml"
                },
                {
                    "nomeTabela": "public.esquematico",
                    "colunas": [
                        "geom"
                    ],
                    "estilo": "esquematico.qml"
                }
            ]
        }
    ]
}

```
## Processos Intermediários 
```
<?php 

    include 'thorMap.php';
    MontarTM('modelo.json');
    echo JSONfromTM('ucm.tm');
    
?>
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

