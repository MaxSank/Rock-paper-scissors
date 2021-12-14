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
    public function generate_hmac($pc_move, $source_key): string
    {
        return hash('sha3-256', ($pc_move. $source_key));
    }
}

class HelpClass
{
    public function show_table($variants): string
    # "Help" table
    {
        foreach ($variants as $value) {
            # Elements on the left
            $temp1 = array();
            for ($i = 0; $i <=count($variants) - 1; $i++) {
                if ($variants[$i] != $value) {
                    array_push($temp1, $variants[$i]);
                }
                elseif ($variants[$i] == $value) {
                    break;
                }
            }

            # Elements on the right
            $temp2 = array_slice($variants, array_search($value, $variants) + 1);

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
    public function user_actions($variants): string
    {
        # Menu
        echo "Available moves:\n";
        for ($i = 0; $i < count($variants); $i++) {
            echo ($i + 1) . " - " . $variants[$i] . "\n";
        }
        echo "0 - exit\n? - help\n";

        # User input
        $user = readline("Enter your move: ");

        # Variants of input
        if ($user > 0 and $user <= count($variants) and $user != '?') {
            echo "Your move: ", $variants[$user - 1],"\n";
            return ($user - 1);
        }

        elseif ($user == 0) {
            exit("Game over.");
        }

        elseif ($user == '?') {
            $help = (new HelpClass)->show_table($variants);
            echo $help;
            return (new UserClass)->user_actions($variants);
        }
        else {
            echo "Invalid input! Try again.\n";
            return (new UserClass)->user_actions($variants);
        }

    }
}

class ResultClass
{
    public function determine_the_winner($pc_move, $user_move, $variants): string
    {
        # String pc_move to index
        $pc_move = array_search($pc_move, $variants);

        # Draw condition
        if ($pc_move == $user_move) {
            return "Draw!\n";
        }

        # Win/lose
        elseif ($pc_move != $user_move) {
            # Remove zero from index
            $pc = $pc_move + 1;
            $user = $user_move + 1;

            # Array with indices of PC win
            $pc_win_indices = array();
            $len = count($variants);
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


# Base script

$variants = array_slice($argv, 1);

# Right insert
if (count($variants) % 2 == 1 and count($variants) >= 3 and count(array_unique($variants)) == count($variants)) {

    # Any number of rounds
    while (true) {
        # Generate key
        $hmac_key = (new SourceKeyClass)->generate_source_key();

        # PC turn
        $pc = array_rand($variants, 1);
        $pc = $variants[$pc];

        # Visible HMAC
        $hmac_pc = (new HmacClass)->generate_hmac($pc, $hmac_key);
        echo "HMAC:\n", $hmac_pc, "\n";

        # Menu and user turn
        $user = (new UserClass)->user_actions($variants);

        # PC choose:
        echo "Computer move: $pc\n";

        # Result
        $result = (new ResultClass)->determine_the_winner($pc, $user, $variants);
        echo $result;

        # Key
        echo "HMAC key:\n", $hmac_key, "\n\n\n";
    }

}

# Messages about problems
elseif (count($variants) % 2 == 0) {
    echo "Please enter an odd number of arguments. For example: 'rock paper scissors'.";
}
elseif (count($variants) < 3) {
    echo "Please enter an odd number of arguments equal to or greater than 3. For example 'rock paper scissors'.";
}
elseif (count(array_unique($variants)) != count($variants)) {
    print_r($variants);
    echo "Please avoid duplicate arguments. For example: 'rock paper scissors'.";
}