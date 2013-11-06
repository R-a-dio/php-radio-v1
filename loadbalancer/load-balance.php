<?php
        if ($_SERVER["SERVER_PORT"] == 443)
                $protocol = "https:";
        else
                $protocol = "http:";


        if (!isset($_GET["debug"]))
                header("Location: $protocol//r-a-d.io/main.mp3");
        else
                echo "Location: $protocol//r-a-d.io/main.mp3";


