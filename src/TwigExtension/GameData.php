<?php
namespace Drupal\game_data\TwigExtension;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

class GameData extends \Twig_Extension {

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'game_data';
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('game_data', array($this, 'gamedata'), array('is_safe' => array('html')))
    ];
  }

  /**
   * In this function we can declare the extension function
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('game_data',
        array($this, 'gamedata'),
        array('is_safe' => array('html')
      )));
  }

  /**
   * The php function to load a given block
   */
  public function gamedata($game) {

    // $fields  =\Drupal::entityManager()->getFieldDefinitions('node','game');

    $entity = Node::load($game);
    $teamNames = [];
    $teams = [];
    $i = '';
    for ($i=1; $i < 3; $i++) {
      $team_field = 'field_team_' . $i . '_data';
      $player_field = 'field_team_' . $i . '_player_data';

      $teamData = $entity->{$team_field}->getValue();
      foreach ( $teamData as $element ) {
        $p = \Drupal\paragraphs\Entity\Paragraph::load($element['target_id']);
        $team = Node::load($p->field_team->getValue()[0]['target_id']);
          $teams[$i]['team']        = $team->title->getValue()[0]['value'];
          $teams[$i]['coach']       = $team->field_team_coach->getValue()[0]['value'];
          $teams[$i]['p1']          = $p->field_p1->getValue()[0]['value'];
          $teams[$i]['p2']          = $p->field_p2->getValue()[0]['value'];
          // $teams[$i]['p3']          = $p->field_p3->getValue()[0]['value'];

          // $teams[$i]['otl']         = $p->field_otl->getValue()[0]['value'];

          if (strlen($p->field_otl->getValue()[0]['value']) < 1) {
             $teams[$i]['otl']         = ' - ';
          }
           else {
             $teams[$i]['otl']         = $p->field_otl->getValue()[0]['value'];
           }

          $teams[$i]['final_score'] = $p->field_final_score->getValue()[0]['value'];
      }

          if (is_object($team)) {
            $teamNames[$i]          = $team->title->getValue()[0]['value'];
            $teamsCoach[$i]         = $team->field_team_coach->getValue()[0]['value'];
          }

      $playerData = $entity->{$player_field}->getValue();
      foreach ( $playerData as $element ) {
        $p = \Drupal\paragraphs\Entity\Paragraph::load($element['target_id']);
        $playerID = $p->field_player_data->getValue()[0]['target_id'];
        $pl = Node::load($playerID);

        $pos = $pl->field_player_position->getValue()[0]['value'];

        ksm($pl->field_player_position->getValue()[0]['value']);

          if ($pl->field_player_position->getValue()[0]['value'] != 'G') {
            $goals   = $p->field_player_goals->getValue()[0]['value'];
            $assists = $p->field_player_assists->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['player']  = $pl->title->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['playerNo']= $pl->field_player_number->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['playerPo']= $pos;
              $teams[$i][$playerID][$pos]['pim']     = $p->field_player_pim->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['assists'] = $p->field_player_assists->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['goals']   = $p->field_player_goals->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['points']  = $goals + $assists;
          }
            else {

            $sog      = $p->field_player_sog->getValue()[0]['value'];
            $ga       = $p->field_player_ga->getValue()[0]['value'];

            $saves    = $sog - $ga;
            $saveperc = $saves/$sog;

              $teams[$i][$playerID][$pos]['player']  = $pl->title->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['playerNo']= $pl->field_player_number->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['playerPo']= $pos;
              $teams[$i][$playerID][$pos]['pim']     = $p->field_player_pim->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['ga']      = $p->field_player_ga->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['pim']     = $p->field_player_pim->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['sog']     = $p->field_player_sog->getValue()[0]['value'];
              $teams[$i][$playerID][$pos]['saves']   = $saves;
              $teams[$i][$playerID][$pos]['svperc']  = $saveperc;
          }

      }
    }


    $game = [];
    $game['title'] = $entity->get('title')->getValue()[0]['value'];

    $division = $entity->field_game_division->getValue()[0]['target_id'];

    $game['rink']     = $entity->get('field_game_rink')->getValue()[0]['value'];
    $game['summary']  = $entity->get('field_game_summary')->getValue()[0]['value'];
    $game['division'] = Term::load($division)->get('name')->value;
    $game['team_1_data']  = $entity->get('field_team_1_data')->getValue()[0]['target_id'];
    $game['team_1_player_data']  = $entity->get('field_team_1_data')->getValue()[0]['target_id'];

    $game['team_2_data']  = $entity->get('field_team_1_data')->getValue()[0]['target_id'];
    $game['team_2_player_data']  = $entity->get('field_team_1_data')->getValue()[0]['target_id'];


    $return_summary = $game['title'] . '<br>';
    $return_summary .= 'division: ' . $game['division'] . '<br>';
    $return_summary .= 'rink: ' . $game['rink'] . '<br>';
    $return_summary .= 'summary: ' . $game['summary'] . '<br>';

    $return_summary .= '<table>';
    // $return_summary .= '<tr><th>Team</th><th>P1</th><th>P2</th><th>P3</th><th>OTL</th><th>Total</th></tr>';
    $return_summary .= '<tr><th>Team</th><th>P1</th><th>P2</th><th>OTL</th><th>Total</th></tr>';

    foreach ($teams as $key => $value) {
      $return_summary .= '<tr>';
      foreach ($value as $row => $teamvalue) {
        if (!is_array($teamvalue)) {
         $return_summary .= '<td>' . $teamvalue . '</td>';
        }
         else {
           foreach ($teamvalue as $playerrow => $playervalue) {
             $player_summary[$key][] = [$playerrow, $playervalue];
           }
         }
      }
      $return_summary .= '</tr>';
    }
    $return_summary .= '</table>';



    $playerDetails = '';
    $playerData = [];

    for ($i=1; $i < 3; $i++) {

      $player_table = '';
      $goalie_table = '';
      // ksm($player_summary);
      foreach ($player_summary[$i] as $key => $value) {
        // ksm($value);
        if ($value[0] != 'G') {
          $playerData[$i]['position']['player'][] = $value[1];
        }
         else {
          $playerData[$i]['position']['goalie'][] = $value[1];
         }
      }

    $player_table .= '<table>';
    $player_table .= '<tr>
                          <td>#</td>
                          <td>Player</td>
                          <td>Position</td>
                          <td>Goals</td>
                          <td>Assists</td>
                          <td>PIM</td>
                          <td>Points</td>
                      </tr>';
    foreach ($playerData[$i]['position']['player'] as $key => $value) {
      $player_table .= '<tr>';
      $player_table .= '<td>' . $value['playerNo'] . '</td>';
      $player_table .= '<td>' . $value['player'] . '</td>';
      $player_table .= '<td>' . $value['playerPo'] . '</td>';
      $player_table .= '<td>' . $value['goals'] . '</td>';
      $player_table .= '<td>' . $value['assists'] . '</td>';
      $player_table .= '<td>' . $value['pim'] . '</td>';
      $player_table .= '<td>' . $value['points'] . '</td>';
      $player_table .= '</tr>';
    }
    $player_table .= '</table>';

    $goalie_table .= '<table>';
    $goalie_table .= '<tr>
                          <td>#</td>
                          <td>Goalie</td>
                          <td>PIM</td>
                          <td>Shots on Goal</td>
                          <td>Goals Against</td>
                          <td>Saves</td>
                          <td>Save %</td>
                      </tr>';
  if (count($playerData[$i]['position']['goalie']) > 0) {
    foreach ($playerData[$i]['position']['goalie'] as $key => $value) {
      $goalie_table .= '<tr>';
      $goalie_table .= '<td>' . $value['playerNo'] . '</td>';
      $goalie_table .= '<td>' . $value['player'] . '</td>';
      $goalie_table .= '<td>' . $value['pim'] . '</td>';
      $goalie_table .= '<td>' . $value['ga'] . '</td>';
      $goalie_table .= '<td>' . $value['sog'] . '</td>';
      $goalie_table .= '<td>' . $value['saves'] . '</td>';
      $goalie_table .= '<td>' . $value['svperc'] . '</td>';
      $goalie_table .= '</tr>';
    }
  }
    $goalie_table .= '</table>';

    $playerDetails .= '<strong>Team ' . $teamNames[$i] . '</strong>';
    $playerDetails .= 'Coach: ' . $teamsCoach[$i];
    $playerDetails .= $coach;
    $playerDetails .= $player_table;
    $playerDetails .= $goalie_table;
   }


    $return_summary = $return_summary . '<br><br>' . $playerDetails;

    return  $return_summary;


  }



}
