<?php if ($this->_var['vote']): ?>
<div class="sideblock" id="ECS_VOTE">
<div class="sbox-top"></div>
<div class="sidemain">
<h1 class="title2">在线调查</h1>
    <form id="formvote" name="ECS_VOTEFORM" method="post" action="javascript:submit_vote()">
	<table class="vote">
	<tr><td>
    <?php $_from = $this->_var['vote']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'title');if (count($_from)):
    foreach ($_from AS $this->_var['title']):
?>
     <strong><?php echo $this->_var['title']['vote_name']; ?></strong><br />(<?php echo $this->_var['lang']['vote_times']; ?>:<?php echo $this->_var['title']['vote_count']; ?>)
     <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	 </td></tr>
	 <tr><td class="voteli">
     <?php $_from = $this->_var['vote']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'title');if (count($_from)):
    foreach ($_from AS $this->_var['title']):
?>
          <?php $_from = $this->_var['title']['options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['item']):
?>
            <?php if ($this->_var['title']['can_multi'] == 0): ?>
            <input type="checkbox" name="option_id" value="<?php echo $this->_var['item']['option_id']; ?>" />
            <?php echo $this->_var['item']['option_name']; ?> (<?php echo $this->_var['item']['percent']; ?>%)<br />
            <?php else: ?>
            <input type="radio" name="option_id" value="<?php echo $this->_var['item']['option_id']; ?>" />
            <?php echo $this->_var['item']['option_name']; ?> (<?php echo $this->_var['item']['percent']; ?>%)<br />
            <?php endif; ?>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
            <input type="hidden" name="type" value="<?php echo $this->_var['title']['can_multi']; ?>" />
     <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
	 </td></tr>
	 <tr><td align="center">
     <input type="hidden" name="id" value="<?php echo $this->_var['vote_id']; ?>" />
     <input type="submit" name="submit" value="<?php echo $this->_var['lang']['submit']; ?>"  class="but" />
     <input type="reset" value="<?php echo $this->_var['lang']['reset']; ?>" class="but" />
	 </td></tr>
	 </table>
     </form>
  </div>
  <div class="sbox-bottom"></div>
 </div>
<?php endif; ?>