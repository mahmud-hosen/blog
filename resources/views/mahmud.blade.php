<!DOCTYPE html>
<html>
<head>
    <title>Person Form</title>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Submit Person Info</h1>

 <form action="{{ route('person.store') }}" method="POST">
    @csrf

    <label for="name">Name:</label><br>
    <input type="text" name="name" id="name" value="{{ old('name') }}"><br>
    @error('name')
        <span style="color: red;">{{ $message }}</span><br>
    @enderror
    <br>

    <label for="age">Age:</label><br>
    <input type="number" name="age" id="age" value="{{ old('age') }}"><br>
    @error('age')
        <span style="color: red;">{{ $message }}</span><br>
    @enderror
    <br>

    <button type="submit">Submit</button>
</form>

</body>
</html>
