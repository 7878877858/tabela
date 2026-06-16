@extends('layouts.app')

@section('title', 'New Feed')

@section('content')

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">🌾 New Feed</h4>
        <small class="text-muted">
            નવા ચારા / ખોરાકની માહિતી ઉમેરો
        </small>
    </div>

    <a href="{{ route('feeds.index') }}"
       class="btn btn-outline-secondary">
        ← પાછા
    </a>
</div>

<div class="card shadow-sm border-0">

    <div class="card-header bg-success text-white">
        🌾 નવી ફીડ ઉમેરો
    </div>

    <div class="card-body">

        <form method="POST"
              action="{{ route('feeds.store') }}">
            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        ફીડ નામ *
                    </label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="જેમ કે લીલો ચારો">
                </div>

                <div class="row">

  

    

    <div class="col-md-4 mb-3">
        <label class="form-label">
            Unit
        </label>

        <select name="unit" class="form-control">
            <option value="Kg">Kg</option>
            <option value="Gram">Gram</option>
            <option value="Liter">Liter</option>
            <option value="Bag">Bag</option>
            <option value="Packet">Packet</option>
            <option value="Bundle">Bundle</option>
            <option value="Piece">Piece</option>
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">
            ઉપલબ્ધ જથ્થો
        </label>

        <input type="number"
               step="0.01"
               name="volume"
               class="form-control"
               placeholder="5000">
    </div>

</div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">
                        વર્ણન
                    </label>

                    <textarea name="description"
                              rows="4"
                              class="form-control"
                              placeholder="ફીડ વિશે નોંધ લખો..."></textarea>
                </div>

                <div class="col-md-12">
                    <label class="form-label">
                        સ્થિતિ
                    </label>

                    <select name="status"
                            class="form-control">

                        <option value="1">Active</option>
                        <option value="0">Inactive</option>

                    </select>
                </div>

            </div>

            <hr>

            <button type="submit"
                    class="btn btn-success">
                <i class="fa fa-save"></i>
                સેવ કરો
            </button>

            <a href="{{ route('feeds.index') }}"
               class="btn btn-light">
                રદ કરો
            </a>

        </form>

    </div>

</div>

@endsection