<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Picker</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>

<form action="" method="post">
    <input type="text" id="datepicker" name="date_range">
    <button type="submit">Submit</button>
</form>

<script>
  flatpickr("#datepicker", {
    mode: "range",
    dateFormat: "Y-m-d", // Format tanggal yang diinginkan
    onChange: function(selectedDates, dateStr, instance) {
        // Optionally handle changes or validate the date range
    }
  });
</script>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the date range from the form submission
    $dateRange = $_POST['date_range'];
    
    // Split the date range into start and end dates
    $dates = explode(' to ', $dateRange);
    $startDate = isset($dates[0]) ? trim($dates[0]) : null;
    $endDate = isset($dates[1]) ? trim($dates[1]) : null;
    
    // Process the dates as needed
    echo "Start Date: " . htmlspecialchars($startDate) . "<br>";
    echo "End Date: " . htmlspecialchars($endDate);
}
?>


</body>
</html>
