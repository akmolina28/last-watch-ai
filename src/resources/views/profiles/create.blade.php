@extends('layout')

@section('content')
{{--    @dd($errors)--}}
    <nav class="breadcrumb" aria-label="breadcrumbs">
        <ul>
            <li><a href="/">Home</a></li>
            <li><a href="/profiles">Detection Profiles</a></li>
            <li class="is-active"><a href="#" aria-current="page">Add Profile</a></li>
        </ul>
    </nav>
    <div id="wrapper">
        <div id="page" class="container">
            <h1 class="heading has-text-weight-bold is-size-4">New Detection Profile</h1>

            <form method="POST" action="/profiles" enctype="multipart/form-data">
                @csrf

                <div class="field">
                    <label class="label" for="name">Name</label>

                    <div class="control">
                        <input
                            class="input @error('name') is-danger @enderror"
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}">
                        @error('name')
                            <p class="help is-danger">{{ $errors->first('name') }}</p>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="file_pattern">File Pattern</label>

                    <div class="control">
                        <input
                            class="input @error('file_pattern') is-danger @enderror"
                            type="text"
                            name="file_pattern"
                            id="file_pattern"
                            placeholder="*.jpg"
                            value="{{ old('file_pattern') }}">
                        @error('file_pattern')
                            <p class="help is-danger">{{ $errors->first('file_pattern') }}</p>
                        @enderror
                    </div>
                </div>

                <div class="field mb-5">
                    <label class="label" for="use_regex">Use Regex</label>

                    <div class="control">
                        <input type="checkbox" name="use_regex" id="use_regex">
                    </div>
                </div>

                <h2 class="heading has-text-weight-bold is-size-5">Relevance</h2>

                <div class="field">
                    <label class="label" for="object_classes[]">Object Classes</label>

                    <div class="control select is-multiple @error('object_classes') is-danger @enderror">
                        {{ Form::select('object_classes[]', config("app.deepstack_object_classes"), old("object_classes[]"), ['multiple']) }}
                        @error('object_classes')
                            <p class="help is-danger">{{ $errors->first('object_classes') }}</p>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="mask">Mask File</label>

                    <div class="file">
                        <label class="file-label">
                            <input class="file-input" type="file" name="mask" accept="image/x-png">
                            <span class="file-cta">
                                <span class="file-icon">
                                    <i class="fas fa-upload"></i>
                                </span>
                                <span class="file-label">
                                    Choose a file…
                                </span>
                            </span>
                        </label>
                    </div>
                </div>


                <div class="field">
                    <label class="label" for="min_confidence">Minimum Confidence</label>

                    <div class="control">
                        <input
                            class="input @error('min_confidence') is-danger @enderror"
                            type="text"
                            name="min_confidence"
                            id="min_confidence"
                            value="{{ old("min_confidence") ?? '0.45' }}">
                        @error('min_confidence')
                        <p class="help is-danger">{{ $errors->first('min_confidence') }}</p>
                        @enderror
                    </div>
                </div>

                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" type="submit">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
