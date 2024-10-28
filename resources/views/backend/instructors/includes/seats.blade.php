

<div class="row">
    <div class="col-sm-5">
        <div>Seats available: <span id="seats_available">{{$freeSeats}}</span> Seats used: <span id="seats_used">{{$totalSeats - $freeSeats}}</span>
            @if (!$hideButton)
                <button class="btn conv-blue conv-next-btn">Add seats</button>
            @endif
        </div>
    </div><!-- col-sm-5 -->
</div><!--row-->