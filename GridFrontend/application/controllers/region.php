<?php

class Region extends Controller {

    function Region()
    {
        parent::Controller();
        $this->load->library('table');
        $this->load->library('SimianGrid');
        $this->load->library('table');

        $this->load->helper('form');
        $this->load->helper('simian_view_helper');
        $this->load->helper('simian_facebook_helper');
        
        $this->lang->load('simian_grid', get_language() );
    }
    
    function view_coord()
    {    
        $data = array();
        if ( $this->input->post('x') && $this->input->post('y') ) {
            $data['x'] = ($this->input->post('x') * 256) + 1;
            $data['y'] = ($this->input->post('y') * 256) + 1;
            $data['scene_data'] = $this->simiangrid->get_scene_by_pos($data['x'], $data['y']);
            $data['uuid'] = $data['scene_data']['SceneID'];
            $data['inline'] = true;
            $this->_scene_extra_info($data['uuid'], $data);
        }
        return parse_template('region/details_popup', $data, true);
    }
    
    function _init_map(&$data)
    {
        if ( $this->config->item('map_x') ) {
               $data['x'] = $this->config->item('map_x');
           } else {
               $data['x'] = 1000;
           }

        if ( $this->config->item('map_y') ) {
            $data['y'] = $this->config->item('map_y');
        } else {
            $data['y'] = 1000;
        }
       
        if ( $this->config->item('zoom') ) {
            $data['zoom'] = $this->config->item('zoom');
        } else {
            $data['zoom'] = 4;
        }
        
        if ( $this->config->item('tile_host') ) {
            $data['tile_host'] = $this->config->item('tile_host');
        } else {
            $data['tile_host'] = "/Grid/map.php/";
        }
    }

    function map()
    {
        $data = array();
        $this->_init_map($data);
        return parse_template('region/map', $data, true);
    }

    function index()
    {
        $data = array();
        $this->_init_map($data);
        $data['page'] = 'regions';
        return parse_template('region/index', $data);
    }
    
    function view($uuid, $extra=null)
    {    
        $data = array();
        $data['scene_data'] = $this->simiangrid->get_scene($uuid);
        if ( $data['scene_data'] == null ) {
            $data['scene_data'] = $this->simiangrid->get_scene_by_name($uuid);
            if ( $data['scene_data'] != null ) {
                $uuid = $data['scene_data']['SceneID'];
            } else {
                push_message(set_message('sg_region_unknown', $uuid), 'error');
                return redirect('region');
            }
        }
        $data['uuid'] = $uuid;
        $data['tab'] = '';
        if ( $extra == "stats" ) {
            $data['tab'] = 'stats';
        }
        $this->_scene_extra_info($uuid, $data);
        $data['title'] = $data['scene_data']['Name'];
        $data['page'] = 'regions';
		$x = $data['scene_data']['MinPosition']['0'] / 256;
		$y = $data['scene_data']['MinPosition']['1'] / 256;
		$data['meta'] = generate_open_graph(site_url("region/view/$uuid"), $this->config->item('grid_name_short') . " region " . $data['scene_data']['Name'], $this->config->item('tile_host') . "map-1-$x-$y-objects.png", "simulator");
        parse_template('region/view', $data);
    }

    function details($uuid, $extra=null)
    {
        $data = array();
        $data['scene_data'] = $this->simiangrid->get_scene($uuid);
        $this->_scene_extra_info($uuid, $data);
        
        $data['inline'] = false;
        $sim_details = $this->simiangrid->simulator_details($uuid);
        if ( $sim_details != null ) {
            $data['region_version'] = $sim_details['version'];
            $data['region_uptime'] = $sim_details['uptime'];
        } else {
            $data['region_version'] = 'N/A';
            $data['region_uptime'] = 'N/A';
        }
        if ( $data['scene_data']['Enabled'] ) {
            $data['online'] = lang('sg_region_online');
        } else {
            $data['online'] = lang('sg_region_offline');
        }
        if ( $extra == "inline" ) {
            if ( $this->input->post('is_search') !== null ) {
                $data['center_map'] = true;
            }
            return parse_template('region/details_popup', $data, true);
        } else {
            return parse_template('region/details', $data, true);
        }
    }
    
    function stats($uuid, $extra=null)
    {
        $data = array();
        $data['scene_id'] = $uuid;
        $data['scene_data'] = $this->simiangrid->get_scene($uuid);
        
        $this->table->set_heading(lang('sg_region_stat_name'), lang('sg_region_stat_value'));
        
        if ( $data['scene_data']['Enabled'] ) {
            $details = $this->simiangrid->simulator_details($uuid);
            if ( $details != null && is_array($details) ) {
                foreach ( $details as $key => $value ) {
                    if ( $key != 'version' && $key != 'uptime' ) {
                        $this->table->add_row(lang("sg_stat_$key"), $value);
                    }
                }
            }
        }        return parse_template('region/stats', $data, true);
    }
    
    function _scene_extra_info($uuid, &$data)
    {    
        $grid_user = $this->simiangrid->get_user($data['scene_data']['ExtraData']['EstateOwner']);
        $data['owner_name'] = $grid_user['Name'];
        $data['owner_id'] = $grid_user['UserID'];
    }

    function _render_region_popup($scene)
    {
        return anchor('region/view/' . $scene['id'], $scene['name'], array('class'=>'search_result','onclick' => 'load_search_result(\'' . $scene['id'] . '\'); return false;'));
    }

    function _truncate_search($search_results, $offset, $page_count)
    {
        $results = array();
        $offset_count = 0;
        $result_count = 0;
        foreach ( $search_results as $search_result ) {
            if ( $offset_count >= $offset && $result_count < $page_count ) {
                $search_item = array(
                    $this->_render_region_popup($search_result)
                );
                array_push($results, $search_item);
                $result_count = $result_count + 1;
            } else {
                $offset_count = $offset_count + 1;
            }
        }
        return $results;
    }

    function search()
    {
        parse_str($_SERVER['QUERY_STRING'],$_GET); 
        $offset = $_GET['iDisplayStart'];
        $limit = $_GET['iDisplayLength'];
        $search = $_GET['sSearch'];
        if ( $search == '' || strlen($search) <= 3 ) {
            $trunc_count = 0;
            $trunc_results = array();
        } else {
            $search_results = $this->simiangrid->search_scene($search);
            $trunc_results = $this->_truncate_search($search_results, $offset, $limit);
            $trunc_count = count($search_results);
        }
        $result = array(
            "sEcho" => $_GET['sEcho'],
            "iTotalRecords" => $this->simiangrid->total_scene_count(),
            "iTotalDisplayRecords" => $trunc_count,
            "aaData" => $trunc_results
        );
        echo json_encode($result);
        return;
    }
}
