@extends('layouts.app')

@section('content')

<form>
	@csrf
	@foreach($inquiries as $inquiry)
	
    Answers<br>
    <div lass="form-group">{{ $inquiry['updated_at'] }}</div><br>

    <div class="form-group">
        <label for="ans_subject">Subject</label>
        <input type="text" class="form-control" name="ans_subject" id="" value="{{ $inquiry['ans_subject'] }}" aria-describedby="helpId" readonly="">
    </div><br>

    <div class="form-group">
        <label for="answer">Answer</label>
        <textarea class="form-control" name="answer" id="" rows="3" readonly>{{ $inquiry['answers'] }}</textarea>
    </div><br>
    

    Your Inquiry<br>
    <div lass="form-group">{{ $inquiry['created_at'] }}</div><br>
    <div class="form-group">
        <label for="inq_subject">Subject</label>
        <input type="text" class="form-control" name="inq_subject" value="{{ \App\Models\Inquiries::getInqSubjectLabels()[$inquiry['inq_subject']] ?? '不明な種別' }}
" id="" aria-describedby="helpId" readonly="">
    </div><br>

    <div class="form-group">
        <label for="inquiry_details">Inquiry Detail</label>
        <textarea class="form-control" name="inquiry_details" id="" rows="3" readonly="">{{ $inquiry['inquiry_details'] }}</textarea>
    </div><br>
    
    @endforeach

</form>

@endsection