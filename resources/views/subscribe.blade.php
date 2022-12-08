@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Subscribe') }}</div>

                <div class="card-body">
                    <form action="{{route('subscribe.store')}}" method="post" id="paymentForm">
                        @csrf
                        <div class="row">
                            <div class="form-group col-12 col-md-6">
                                <label for="first-name">First Name</label>
                                <input type="text" class="form-control" id="first-name" name="first_name" required />
                            </div>
                            <div class="form-group col-12 col-md-6">
                                <label for="last-name">Last Name</label>
                                <input type="text" class="form-control" id="last-name" name="last_name" required />
                            </div>
                            <div class="form-group col-12">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required />
                            </div>
                        </div>
                        {{-- Subscription Plans --}}
                        <div class="form-group">
                            <label>Select your Plan</label>
                            <br />
                            @foreach ( $plans as $plan )
                                <label 
                                    for="{{$plan["slug"]}}"
                                >
                                    <input type="radio"
                                        name="plan"
                                        id="{{$plan["slug"]}}"
                                        value="{{$plan["slug"]}}"
                                        required
                                    >
                                    <span class="text-capitalize mr-5">{{$plan["slug"]}} ({{$plan["visual_price"]}})</span>
                                </label>
                            @endforeach
                        </div>

                        {{-- Payment Platforms --}}
                        <div class="form-group">
                            <label>Select the desired payment platform</label>
                            <div class="form-group" id="toggler">
                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    @foreach ( $paymentPlatforms as $paymentPlatform )
                                        <label 
                                            for="{{$paymentPlatform["name"]}}"
                                            class="btn btn-outline-secondary rounded m-2 p-1 label-platforms"
                                            data-target="#{{$paymentPlatform["name"]}}Collapse"
                                            data-toggle="collapse"
                                        >
                                            <input type="radio"
                                                name="payment_platform"
                                                id="{{$paymentPlatform["name"]}}"
                                                value="{{$paymentPlatform["name"]}}"
                                                class="w-0"
                                                required
                                            >
                                            <img
                                                class="img-thumbnail"
                                                src="{{asset($paymentPlatform["image"])}}"
                                                alt="{{$paymentPlatform["name"]}}"
                                            >
                                        </label>
                                        
                                    @endforeach
                                </div>
                            </div>
                            @foreach ( $paymentPlatforms as $paymentPlatform )
                                <div class="form-group collapse" id="{{$paymentPlatform["name"]}}Collapse" data-parent="#toggler">
                                    @includeIf('components.'. strtolower($paymentPlatform["name"]) . '-collapse')
                                </div>
                            @endforeach
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block" id="payButton">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
