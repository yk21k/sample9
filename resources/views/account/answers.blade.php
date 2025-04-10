@extends('layouts.app')

@section('content')

<br><br><br>
運営者からのご連絡（Shopの開設申し込みいただいた際の開設可否についてもこちらでご案内）
<br>返金、返品については、お客様とShopで直接のやり取りして下さい。

<strong>回答ページのためこちらからはお問い合わせ不可</strong>
<br><br><br><br>Q＆Aの例示<br>
<br><br>
    回答<br>
    <div lass="form-group">回答日時</div><br>

    <div class="form-group">
        <label for="ans_subject">件名</label>
        <input desabled type="text" class="form-control" name="ans_subject" id="" value="" aria-describedby="helpId" readonly="" placeholder='件名'>
    </div><br>

    <div class="form-group">
        <label for="answer">回答</label>
        <textarea class="form-control" name="answer" id="" rows="3" readonly>回答内容</textarea>
    </div><br>

    あなたの質問<br>
    <div lass="form-group">質問日時</div><br>
    <div class="form-group">
        <label for="inq_subject">件名</label>
        <input desabled type="text" class="form-control" name="inq_subject" value="" id="" aria-describedby="helpId" readonly="" placeholder='件名'>
    </div><br>

    <div class="form-group">
        <label for="inquiry_details">質問</label>
        <textarea class="form-control" name="inquiry_details" id="" rows="3" readonly="">質問内容</textarea>
    </div><br>

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
        <input type="text" class="form-control" name="inq_subject" value="{{ $inquiry['inq_subject'] }}" id="" aria-describedby="helpId" readonly="">
    </div><br>

    <div class="form-group">
        <label for="inquiry_details">Inquiry Detail</label>
        <textarea class="form-control" name="inquiry_details" id="" rows="3" readonly="">{{ $inquiry['inquiry_details'] }}</textarea>
    </div><br>
    
    @endforeach

</form>

@endsection