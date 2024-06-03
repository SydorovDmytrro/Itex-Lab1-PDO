<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Car Rental Management</h1>
        
        <form method="POST" action="index.php">
            <div class="form-group">
                <label for="date">Select Date for Revenue:</label>
                <input type="date" id="date" name="date">
            </div>
            <div class="form-group">
                <label for="manufacturer">Select Manufacturer:</label>
                <input type="text" id="manufacturer" name="manufacturer" placeholder="Enter manufacturer name">
            </div>
            <div class="form-group">
                <label for="free_date">Select Date for Free Cars:</label>
                <input type="date" id="free_date" name="free_date">
            </div>
            <button type="submit" name="submit">Submit</button>
        </form>

        <div class="results">
            <?php
            if (isset($_POST['submit'])) {
                $date = $_POST['date'];
                $manufacturer = $_POST['manufacturer'];
                $free_date = $_POST['free_date'];

                // Database connection
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "lb_pdo_rent";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Query for revenue
                if (!empty($date)) {
                    $stmt = $conn->prepare("SELECT SUM(Cost) AS total_revenue FROM rent WHERE Date_end <= ?");
                    $stmt->bind_param("s", $date);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        echo "<h2>Total Revenue by $date: " . $row['total_revenue'] . "</h2>";
                    } else {
                        echo "<h2>No revenue data found for $date.</h2>";
                    }
                    $stmt->close();
                }

                // Query for cars by manufacturer
                if (!empty($manufacturer)) {
                    $stmt = $conn->prepare("SELECT * FROM cars WHERE FID_Vendors = (SELECT ID_Vendors FROM vendors WHERE Name = ?)");
                    $stmt->bind_param("s", $manufacturer);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        echo "<h2>Cars by $manufacturer:</h2>";
                        echo "<ul>";
                        while($row = $result->fetch_assoc()) {
                            echo "<li>" . $row['Name'] . " (" . $row['Release_date'] . ") - $" . $row['Price'] . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<h2>No cars found for manufacturer $manufacturer.</h2>";
                    }
                    $stmt->close();
                }

                // Query for free cars on selected date
                if (!empty($free_date)) {
                    $stmt = $conn->prepare("SELECT * FROM cars WHERE ID_Cars NOT IN (SELECT FID_Car FROM rent WHERE ? BETWEEN Date_start AND Date_end)");
                    $stmt->bind_param("s", $free_date);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        echo "<h2>Free Cars on $free_date:</h2>";
                        echo "<ul>";
                        while($row = $result->fetch_assoc()) {
                            echo "<li>" . $row['Name'] . " (" . $row['Release_date'] . ") - $" . $row['Price'] . "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<h2>No free cars available on $free_date.</h2>";
                    }
                    $stmt->close();
                }

                $conn->close();
            }
            ?>
        </div>
    </div>
</body>
</html>

