<div class="sideblock side-ask" style="margin-top:0;">
<div class="sbox-top"></div>
<div class="sidemain">
<h1 class="title2">本单答疑</h1>
<p class="link"><a href="<?php echo $this->_var['ask_url']; ?>">查看全部(<?php echo $this->_var['comment_num']; ?>)</a> | <a href="<?php echo $this->_var['ask_url']; ?>">我要提问</a></p>
<ul>
<?php $_from = $this->_var['group_comment']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'comment_0_61681400_1336623097');if (count($_from)):
    foreach ($_from AS $this->_var['comment_0_61681400_1336623097']):
?>
<li><a href="<?php echo $this->_var['comment_0_61681400_1336623097']['url']; ?>"><?php echo sub_str($this->_var['comment_0_61681400_1336623097']['content'],30); ?></a></li>
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</ul>
</div>
<div class="sbox-bottom"></div>
</div>
