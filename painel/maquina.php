<?php
    session_start();
    require_once "../includes/servidor.php";

    

    if(isset($_POST['maquina'])){
        $maquina = $_POST['maquina'];

        $sql = "INSERT INTO maquinas (nome) VALUE (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $maquina);

        if ($stmt->execute()) {
            header("Location: inicio.php");
            exit();
        } else {
            echo "Erro ao adicionar máquina: " . $conn->error;
        }
    }

    if(isset($_POST['id'])){
        $id = intval ($_POST['id']);

        $sql = "DELETE FROM maquinas WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);


        if($stmt->execute()){
            header("Location:inicio.php");
            exit;
        }else{
            echo "Erro ao excluir a maquina" . $conn->error;
        }
    }else{
        echo "ID da maquina não informada";
    }
    

//     if($_SERVER['REQUEST_METHOD'] === 'POST'){
//         $id_maquina = intval($_POST['id_maquina']);
//         $tempo_segundos = intval($_POST['tempo_segundos']);

//         if($id_maquina > 0 && $tempo_segundos > 0){
//             $fim = date('y-m-d h:i:s');

//             $query_inicio = $pdo->prepare("SELECT inicio FROM maquinas WHERE id=?");
//             $query_inicio->execute([$id_maquina]);
//             $row = $query_inicio->fetch(PDO::FETCH_ASSOC);

//             if($row && $row['inicio']){
//                 $inicio = $row['inicio'];

//                 $stmt = $pdo->prepare("SELECT INTO tempo_uso(maquina_id, inicio, fim, tempo_segundos) VALUES(?, ?, ?, ?)");
//                 $stmt->execute([$id_maquina, $inicio, $fim, $tempo_segundos]);

//                 $update = $pdo->prepare("UPDATE maquinas SET status = 'livre', inicio = NULL WHERE id = ?");
//                 $update->execute([$id_maquina]);

//                 echo json_encode(['status' => 'ok', 'mensagem' => 'Tempo salvo com sucesso!']);
//             }else{
//                 echo json_encode(['status' => 'erro', 'mensagem' => 'Início da máquina não encontrado.']);
//             }
//         }else{
//             echo json_encode(['status' => 'erro', 'mensagem' => 'Dados inválidos.']);
//         } 
//     }
    
// ?>