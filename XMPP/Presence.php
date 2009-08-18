<?php
/**
 * Models a presence packet for sending and receiving
 * @author Paul Visco
 * @version 1.0 05-12-2009 05-12-2009
 */
class sb_XMPP_Presence extends sb_XMPP_Packet{

    public function get_from(){
       return $this['from'];
    }

    public function get_status(){
        if(isset($this->status[0])){
            return $this->status[0];
        } else {
            return '';
        }
    }

    public function get_show(){
        if(isset($this->show[0])){
            return $this->show[0];
        } else {
            return '';
        }
    }

    public function get_type(){
       return $this['type'];
    }

    public function get_priority(){
        if(isset($this->priority[0])){
            return $this->priority[0];
        } else {
            return '';
        }
    }

    public function set_type($type){
        $this['type'] = $type;
    }

    public function set_status($status){
        $this->addChild('status', htmlspecialchars($status));
    }

    public function set_show($show){

        if($show == 'unavailable') {
            $this->set_type($show);
        }

        $show = htmlspecialchars($show);
        if(isset($this->show[0])){
            $this->show[0] = $show;
        } else {
            $this->addChild('show', $show);
        }

    }

    public function set_priority($priority=1){
        $this->addChild('priority', htmlspecialchars($priority));
    }

    public function set_to($to){
         $this->addAttribute('to', htmlspecialchars($to));
    }

    public function set_from($from){
        $this->addAttribute('from', htmlspecialchars($from));
    }

    public function set_photo($photo){
       $x = $this->addChild('x');
       $x->addAttribute('xmlns', 'vcard-temp:x:update');
       $p = $x->addChild('photo');
       $p = 'none';
    }

}

?>