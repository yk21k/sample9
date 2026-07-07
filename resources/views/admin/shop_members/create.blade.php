{{-- resources/views/shop_members/create.blade.php --}}

<h1>スタッフ追加</h1>

<form method="POST" action="{{ route('shop_members.store', $shop) }}">
    @csrf

    <div>
        <label>名前</label>
        <input type="text" name="name" required>
    </div>

    <div>
        <label>ユーザーID（任意）</label>
        <input type="number" name="user_id">
    </div>

    <div>
        <label>役割</label>
        <select name="role">
            <option value="staff">staff（閲覧）</option>
            <option value="manager">manager（管理）</option>
        </select>
    </div>

    <button type="submit">追加</button>
</form>