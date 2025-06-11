@uselayout('base')

@section('content')
    Welcome to Orvyn! <br><br>
    <form method='post' action='{{ route('store') }}'>
        <input name='name' placeholder='Enter your name' />
        <button type='submit'>Submit</button>
    </form><br>
    To <a href='{{ route('test', ['param' => 'test']) }}'>test page</a><br>
    @peko("ooTest")<br>
@endsection

@section('script')
    adfasdg
@endsection