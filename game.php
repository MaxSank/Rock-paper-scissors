<?php

class SourceKeyClass
{
    public function generate_source_key(): string
    {
        return bin2hex(random_bytes(256));
    }
}

class HmacClass
{
    public function generate_hmac(string $str_with_move_chosen_by_pc, string $str_with_source_key): string
    {
        return hash('sha3-256', ($str_with_move_chosen_by_pc. $str_with_source_key));
    }
}

class HelpClass
{
    public function show_table(array $arr_of_variants): string
    # "Help" table
    {
        foreach ($arr_of_variants as $value) {
            # Items to the left of the selected item
            $temp1 = array();
            for ($i = 0; $i <=count($arr_of_variants) - 1; $i++) {
                if ($arr_of_variants[$i] != $value) {
                    array_push($temp1, $arr_of_variants[$i]);
                }
                elseif ($arr_of_variants[$i] == $value) {
                    break;
                }
            }

            # Items to the right of the selected item
            $temp2 = array_slice($arr_of_variants, array_search($value, $arr_of_variants) + 1);

            # Merge two parts
            $temp = array_merge($temp2, $temp1);

            # Discarding losing combinations
            $temp = array_slice($temp, count($temp) / 2);

            echo "$value wins if the opponent chooses: ";
            foreach ($temp as $val) {
                echo "$val ";
            }
            echo "\n";
        }
        return "";
    }
}

class UserClass
{
    public function user_actions(array $arr_of_options): string
    {
        # Menu
        echo "Available moves:\n";
        for ($i = 0; $i < count($arr_of_options); $i++) {
            echo ($i + 1) . " - " . $arr_of_options[$i] . "\n";
        }
        echo "0 - exit\n? - help\n";

        # User input
        $user_input = readline("Enter your move: ");

        # Variants of input
        if ($user_input > 0 and $user_input <= count($arr_of_options) and $user_input != '?') {
            echo "Your move: ", $arr_of_options[$user_input - 1],"\n";
            return ($user_input - 1);
        }

        # Exit from game
        elseif ($user_input == 0) {
            exit("Game over.");
        }

        # Calling the help table and recall USER_ACTIONS()
        elseif ($user_input == '?') {
            $help = (new HelpClass)->show_table($arr_of_options);
            echo $help;
            return (new UserClass)->user_actions($arr_of_options);
        }

        # Invalid input
        else {
            echo "Invalid input! Try again.\n";
            return (new UserClass)->user_actions($arr_of_options);
        }

    }
}

class ResultClass
{
    public function determine_the_winner(string $pcs_choice, int $users_choice, array $initial_array): string
    {
        # String pc_move to index
        $pcs_choice = array_search($pcs_choice, $initial_array);

        # Draw condition
        if ($pcs_choice == $users_choice) {
            return "Draw!\n";
        }

        # Win/lose conditions
        elseif ($pcs_choice != $users_choice) {
            # Remove zero from index
            $pc = $pcs_choice + 1;
            $user = $users_choice + 1;

            # Array with indices of PC win
            $pc_win_indices = array();
            $len = count($initial_array);
            for ($i = $user + 1; $i <= ($user + intdiv($len, 2)); $i++) {
                if ($i > $len) {
                    array_push($pc_win_indices, $i - $len);
                }
                else {
                    array_push($pc_win_indices, $i);
                }
            }

            # Checking if PC has won
            if (in_array($pc, $pc_win_indices)) {
                return "Computer win!\n";
            }
            else {
                return "You win!\n";
            }
        }
    return "";
    }
}


# Basic script

$variants = array_slice($argv, 1);

# Right insert
if (count($variants) % 2 == 1 and count($variants) >= 3 and count(array_unique($variants)) == count($variants)) {

    # Any number of rounds through "while (true)"
    while (true) {
        # Generate key
        $hmac_key = (new SourceKeyClass)->generate_source_key();

        # PC's turn
        $pc = array_rand($variants, 1);
        $pc = $variants[$pc];

        # Calculate and print visible HMAC
        $hmac_pc = (new HmacClass)->generate_hmac($pc, $hmac_key);
        echo "HMAC:\n", $hmac_pc, "\n";

        # Menu and user's turn
        $user = (new UserClass)->user_actions($variants);

        # PC choose:
        echo "Computer move: $pc\n";

        # Calculate and print result
        $result = (new ResultClass)->determine_the_winner($pc, $user, $variants);
        echo $result;

        # Print key
        echo "HMAC key:\n", $hmac_key, "\n\n\n";
    }

}

# Messages about problems of input
elseif (count($variants) < 3) {
    echo "Please enter an odd number of arguments equal to or greater than 3. For example 'rock paper scissors'.";
}

elseif (count($variants) % 2 == 0) {
    echo "Please enter an odd number of arguments. For example: 'rock paper scissors'.";
}

elseif (count(array_unique($variants)) != count($variants)) {
    echo "Please avoid duplicate arguments. For example: 'rock paper scissors'.";
}
