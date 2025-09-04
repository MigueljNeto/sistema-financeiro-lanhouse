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

    $id = $_GET['id'];

    mysqli_query($conn, "DELETE FROM tempo_uso WHERE maquina_id = $id");

    mysqli_query($conn, "DELETE FROM maquinas WHERE id = $id");
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
?>