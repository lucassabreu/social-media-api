# Social Media API
Alguns exemplos sobre como chamar o SocialMediaRestAPI.

Abstrato:

Esta aplicação trata de três recursos: Usuário (users), Amizade (friendships) e Postagens (posts). Todo o conteúdo deste aplicativo é aberto a todo aquele que acessar o sistema, parecendo "Twitter" em uma forma muito simples, de modo que a leitura de dados que você não vai precisar autenticar-se, mas quando chega a hora de publicar e fazer amizades, então será necessária autenticação. A autenticação no sistema foi feito usando Authorition Basic, então espera-se que este aplicativo será executado através de HTTPS.

As tecnologias aplicadas a este projecto foi Zend Framework 2, Módulo Doctrine, PHPUnit e zend-mvc-auth para lidar com os cabeçalhos de autorização.

A escolha sobre estas bibliotecas foi baseada no meu conhecimento sobre eles, e também na quantidade de documentação que existe das mesmas.

Mais abaixo estão as funções e formas de uso da solução construída. Essa documentação foi criada com base na seguinte coleção do Postman: [https://www.getpostman.com/collections/ad1379f35c25b4c0a8bc](https://www.getpostman.com/collections/ad1379f35c25b4c0a8bc), a documentação gerada pelo Postman pode ser acessada aqui: [https://documenter.getpostman.com/collection/view/69638-c7efcf2e-dce5-73d0-4596-caf3a7dc5a41](https://documenter.getpostman.com/collection/view/69638-c7efcf2e-dce5-73d0-4596-caf3a7dc5a41)

## POST Criar Novo Usuário
```
http://localhost:8080/api/users
```
Cria um novo usuário no sistema validando para que o mesmo e-mail não seja utilizado duas vezes.

| BODY     |                         |
|---------:|-------------------------|
| name     | Lucas dos Santos Abreu  |
| username | lucas.s.abreu@gmail.com |
| password | 123456                  |


### Exemplo Chamada em cURL
```sh
curl -request POST \
  --url http://localhost:8080/api/users \
  --data 'name=Lucas%20dos%20Santos%20Abreu&username=lucas.s.abreu%40gmail.com&password=123456'
```

## GET Dados do Usuário Logado
```
http://localhost:8080/api/users/self
```
Mostra dados sobre o usuário logado que podem ser utilizados para orientação para a aplicação que vier a consumir o mesmo.

Uma vez que seja informado usuário e senha corretos será retornado um JSON no seguinte formato:

### Retorno: 
```json
{ 
    "result": { 
        "id": 11, 
        "name": "Lucas dos Santos Abreu", 
        "username": "lucas.s.abreu@gmail.com" 
    } 
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url http://localhost:8080/api/users/self \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2'
```

## GET Listar Usuários
```
http://localhost:8080/api/users
```
Retorna os usuário cadastrados no sistema no momento, suporta filtro por nome de usuário e paginação.

| Parâmetros |                                                                                                                                        |
|-----------:|----------------------------------------------------------------------------------------------------------------------------------------|
| limit      | Número de registros por página, limitado a 50                                                                                          |
| offset     | Ponto para iniciar a listagem de registros                                                                                             |
| q          | filtro de página, o valor deve estar no formato: "q=name:Lucas", o sistema irá processar um like no banco o termo %Lucas% dessa forma. |

### Retorno:
```json
{ 
    "result": [ 
        { 
            "id": 1, 
            "name": "Joãozinho" 
        }, 
        { 
            "id": 2, 
            "name": "Lucas dos Santos Abreu" 
        } 
    ], 
    "paging": { 
        "count": 2, 
        "total": 2, 
        "offset": 0 
    } 
}
```

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url http://localhost:8080/api/users
```

## GET Dados de um Usuário
```
http://localhost:8080/api/users/[:id]
```
Retorna as informações do usuári do ID passado.

### Retorno:
```json
{ 
    "result": { 
        "id": 11,
        "name": "Lucas dos Santos Abreu" 
    } 
}
```

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url 'http://localhost:8080/api/users/[:id]'
```

## PUT Modificar Usuário
```
http://localhost:8080/api/users/[:id]
```
Permite alterar o nome do usuário informado pelo ID.

Apenas irá funcionar se o usuário do parâmetro for o mesmo que esta logado.

### Retorno:
```json
{ 
    "result": { 
        "id": 11, 
        "name": "Lucas dos Santos Abreu" 
    } 
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

| BODY |             |
|------|-------------|
| name | Lucas Abreu |

### Exemplo Chamada em cURL
```sh
curl --request PUT \
  --url 'http://localhost:8080/api/users/[:id]' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2' \
  --data name=Lucas%20Abreu
```

## PUT Trocar senha de usuário
```
http://localhost:8080/api/users/[:id]/change-password
```
Permite que seja alterada a senha do usuário do parâmetro.

Apenas irá funcionar se o usuário do parâmetro for o mesmo que esta logado.

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

| BODY        |        |
|-------------|--------|
| password    | 123456 |
| newPassword | 654321 |

### Exemplo Chamada em cURL
```sh
curl --request PUT \
  --url 'http://localhost:8080/api/users/[:id]/change-password' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2' \
  --data 'password=123456&newPassword=654321'
```

## DELETE Remover Usuário
```
http://localhost:8080/api/users/[:id]
```
Permite eliminar um usuário de acordo com o parâmetro.

Apenas irá funcionar se o usuário do parâmetro for o mesmo que esta logado.

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```sh
curl --request DELETE \
  --url 'http://localhost:8080/api/users/[:id]' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2'
```

## POST Criar Amizade
```
http://localhost:8080/api/users/[:userId]/friends
```
Criar uma nova relação de amizade entre o usuário da URL e do parâmetro id.

Apenas irá funcionar se o usuário do parâmetro for o mesmo que esta logado.

### Retorno: 
```json
{ 
    "result": { 
        "id": 8, 
        "name": "Joãozinho" 
    } 
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

| BODY |             |
|-----:|-------------|
| id   | [:idFriend] |

### Exemplo Chamada em cURL
```sh
curl -request POST \
  --url 'http://localhost:8080/api/users/[:userId]/friends' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2' \
  --data id=%5B%3AidFriend%5D
```

## GET Lista Amizades do Usuário
```
http://localhost:8080/api/users/[:id]/friends
```
Permite listar as amizades do usuário da URL

### Retorno: 
```json
{ 
    "result": 
    [ 
        { 
            "id": 8, 
            "name": "Joãozinho" 
        }
    ] 
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url 'http://localhost:8080/api/users/[:id]/friends' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2'
```

## DELETE Desfazer Amizade
```
http://localhost:8080/api/users/[:idUser]/friends/[:idFriend]
```
Permite eliminar uma relação de amizade entre os dois usuários do parâmetro da URL.

Apenas irá funcionar se o usuário do parâmetro for o mesmo que esta logado.

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```sh
curl --request DELETE \
  --url 'http://localhost:8080/api/users/[:idUser]/friends/[:idFriend]' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2'
```

## POST Criar Nova Postagem
```
http://localhost:8080/api/posts
```
Permite criar uma nova Postagem para o usuário autorizado no sistema. Somente é informado o texto da mensagem.

*Apenas irá funcionar se o usuário do parâmetro for o mesmo que esta logado.*

### Retorno: 
```json
{ 
    "result": { 
        "id": 9, 
        "userId": 11, 
        "datePublish": "2016-07-30 03:55:53", 
        "text": "something funny" 
    } 
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

| BODY |                 |
|-----:|-----------------|
| text | something funny |

### Exemplo Chamada em cURL
```sh
curl -request POST \
  --url http://localhost:8080/api/posts \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2' \
  --header 'content-type: multipart/form-data; boundary=---011000010111000001101001' \
  --form 'text=something funny'
```
## GET Listar Postagem
```
http://localhost:8080/api/posts
```
Retorna as Postagens de todo o sistema, essa função permite paginação dos resultados e filtro de texto.

| Parâmetros |                                                                                                                                        |
|-----------:|----------------------------------------------------------------------------------------------------------------------------------------|
| limit      | Número de registros por página, limitado a 50                                                                                          |
| offset     | Ponto para iniciar a listagem de registros                                                                                             |
| q          | filtro de página, o valor deve estar no formato: "q=text:funny", o sistema irá processar um like no banco o termo %funny% dessa forma. |

### Retorno:

```json
{
    "result": 
    [ 
        { 
            "id": 10, 
            "userId": 11, 
            "datePublish": "2016-07-30 03:57:33", 
            "text": "something funny" 
        }, 
        { 
            "id": 9, 
            "userId": 11,
            "datePublish": "2016-07-30 03:55:53", 
            "text": "something funny" 
        }, 
        { 
            "id": 4, 
            "userId": 8, 
            "datePublish": "2016-07-29 23:26:39", 
            "text": "ola" 
        } 
    ], 
    "paging": { 
        "count": 3, 
        "total": 3, 
        "offset": 0 
    } 
}
```

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url http://localhost:8080/api/posts
```
## GET Listar Postagem Filtrando
```
http://localhost:8080/api/posts?q=text:funny
```
Retorna as Postagens de todo o sistema, essa função permite paginação dos resultados e filtro de texto.

| Parâmetros |                                                                                                                                        |
|-----------:|----------------------------------------------------------------------------------------------------------------------------------------|
| limit      | Número de registros por página, limitado a 50                                                                                          |
| offset     | Ponto para iniciar a listagem de registros                                                                                             |
| q          | filtro de página, o valor deve estar no formato: "q=text:funny", o sistema irá processar um like no banco o termo %funny% dessa forma. |

### Retorno:
```json
{ 
    "result": 
    [ 
        { 
            "id": 10, 
            "userId": 11, 
            "datePublish": "2016-07-30 03:57:33", 
            "text": "something funny" 
        }, 
        { 
            "id": 9, 
            "userId": 11, 
            "datePublish": "2016-07-30 03:55:53", 
            "text": "something funny" 
        } 
    ], 
    "paging": { 
        "count": 2, 
        "total": 2, 
        "offset": 0 
    } 
}
```

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url 'http://localhost:8080/api/posts?q=text%3Afunny'
```
## GET Listar Postagens do Usuário
```
http://localhost:8080/api/posts/user/[:id]
```
Permite listar todas as Postagens de *UM* usuário, como um perfil, esta função permite paginação e filtro.

| Parâmetros |                                                                                                                                        |
|-----------:|----------------------------------------------------------------------------------------------------------------------------------------|
| limit      | Número de registros por página, limitado a 50                                                                                          |
| offset     | Ponto para iniciar a listagem de registros                                                                                             |
| q          | filtro de página, o valor deve estar no formato: "q=text:funny", o sistema irá processar um like no banco o termo %funny% dessa forma. |

### Retorno: 
```json
{ 
    "result": 
    [ 
        { 
            "id": 10, 
            "userId": 11, 
            "datePublish": "2016-07-30 03:57:33", 
            "text": "something funny" 
        }, 
        { 
            "id": 9, 
            "userId": 11, 
            "datePublish": "2016-07-30 03:55:53", 
            "text": "something funny" 
        } 
    ], 
    "paging": { 
        "count": 2, 
        "total": 2, 
        "offset": 0 
    } 
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url 'http://localhost:8080/api/posts/user/[:id]' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2'
```
## GET Listar Feed
```
http://localhost:8080/api/feed
```
Retorna o Feed do usuário autenticado, sendo com## POSTa das próprias Postagens do usuário e de seus amigos. É possível usar paginação e filtragem.

| Parâmetros |                                                                                                                                        |
|-----------:|----------------------------------------------------------------------------------------------------------------------------------------|
| limit      | Número de registros por página, limitado a 50                                                                                          |
| offset     | Ponto para iniciar a listagem de registros                                                                                             |
| q          | filtro de página, o valor deve estar no formato: "q=text:funny", o sistema irá processar um like no banco o termo %funny% dessa forma. |

### Retorno: 
```json
{
    "result": [
        {
            "id": 10,
            "userId": 11,
            "datePublish": "2016-07-30 03:57:33",
            "text": "something funny"
        }, {
            "id": 9,
            "userId": 11,
            "datePublish": "2016-07-30 03:55:53",
            "text": "something funny"
        }, {
            "id": 4,
            "userId": 8,
            "datePublish": "2016-07-29 23:26:39",
            "text": "ola"
        }
    ],
    "paging": {
        "count": 3,
        "total": 3,
        "offset": 0
    }
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url http://localhost:8080/api/feed \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2'
```
## GET Detalhar Postagem
```
http://localhost:8080/api/posts/[:id]
```
Retorna a data de publicação, Id do usuário, texto e Id da mesma.

### Retorno: 
```json
{
    "result": {
        "id": 10,
        "userId": 11,
        "datePublish": "2016-07-30 03:57:33",
        "text": "something funny"
    }
}
```

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```sh
curl --request GET \
  --url 'http://localhost:8080/api/posts/[:id]' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2'
```
## PUT Modificar Postagem
```
http://localhost:8080/api/posts/1
```
Modifica o texto da Postagem informada no parâmetro da URL.

*Apenas irá funcionar se o usuário autenticado for o mesmo que esta publicou a Postagem.*

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

| BODY |                |
|-----:|----------------|
| text | not that funny |

### Exemplo Chamada em cURL
```sh
curl --request PUT \
  --url http://localhost:8080/api/posts/1 \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2' \
  --data text=not%20that%20funny
```
## DELETE Elimina Postagem
```
http://localhost:8080/api/posts/[:id]
```
Permite eliminar a Postagem informada na URL.

*Apenas irá funcionar se o usuário autenticado for o mesmo que esta publicou a Postagem.*

| HEADERS       |                                                |
|---------------|------------------------------------------------|
| Authorization | Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2 |

### Exemplo Chamada em cURL
```
curl --request DELETE \
  --url 'http://localhost:8080/api/posts/[:id]' \
  --header 'authorization: Basic bHVjYXMucy5hYnJldUBnbWFpbC5jb206MTIzNDU2' \
  --header 'content-type: multipart/form-data; boundary=---011000010111000001101001'
```  