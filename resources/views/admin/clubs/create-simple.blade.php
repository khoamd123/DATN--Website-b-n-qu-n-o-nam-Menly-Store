<!DOCTYPE html>
<html>
<head>
    <title>Test Create Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Test Create Club</h1>
        
        <div class="alert alert-info">
            <h4>Debug Info:</h4>
            <p><strong>Fields count:</strong> {{ isset($fields) ? $fields->count() : 'UNDEFINED' }}</p>
            <p><strong>Users count:</strong> {{ isset($users) ? $users->count() : 'UNDEFINED' }}</p>
        </div>

        @if(isset($fields) && $fields->count() > 0)
            <div class="alert alert-success">
                <h5>Fields Available:</h5>
                <ul>
                    @foreach($fields as $field)
                        <li>{{ $field->name }} - {{ $field->description }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="alert alert-danger">
                <h5>No Fields Available!</h5>
                <p>Fields variable is not defined or empty.</p>
            </div>
        @endif

        @if(isset($users) && $users->count() > 0)
            <div class="alert alert-success">
                <h5>Users Available:</h5>
                <ul>
                    @foreach($users as $user)
                        <li>{{ $user->name }} ({{ $user->email }})</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="alert alert-warning">
                <h5>No Users Available!</h5>
                <p>Users variable is not defined or empty.</p>
            </div>
        @endif

        <a href="/admin/clubs" class="btn btn-secondary">Back to Clubs</a>
    </div>
</body>
</html>













