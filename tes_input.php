
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tes Maksimal Input</title>
</head>
<body>

    <h3>Tes NIS Maksimal 10 Angka</h3>

    <input
        type="text"
        id="nis"
        maxlength="10"
        oninput="this.value = this.value.slice(0, 10)"
    >

    <p id="hasil">0 / 10 angka</p>

    <script>
        const nis = document.getElementById('nis');
        const hasil = document.getElementById('hasil');

        nis.addEventListener('input', function () {
            this.value = this.value.slice(0, 10);
            hasil.textContent = this.value.length + ' / 10 angka';
        });
    </script>

</body>
</html>
