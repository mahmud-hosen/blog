<!DOCTYPE html>
<html>
<head>
    <title>SFTP File Upload</title>
</head>
<body>
    @if(session('success'))
        <p style="color:green;">{{ session('success') }}</p>
    @endif

    <form action="{{ route('sftp.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Upload to SFTP</button>
    </form>
</body>
</html>
