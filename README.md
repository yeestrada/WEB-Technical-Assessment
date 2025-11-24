WEC Technical Assessment
    Overview
        For this technical assessment you’ll need to implement three major things: FizzBuzz, Fibonacci,
        and a combination of the two. The application(s) must be web-based with a user interface (UI).
        You’re free to choose what tech stack to use and to take advantage of any resources you’d expect to
        have in day-to-day development. Please reach out if there are any questions.
        
    Part 1: Implement FizzBuzz
        Requirements:
            • Allow a user to enter a number, then process all whole integers from 0 to the input number,
            producing the following output:
                o For multiples of 3, display "Fizz".
                o For multiples of 5, display "Buzz".
                o For multiples of both 3 and 5, display "FizzBuzz".
                • Allow the user to create alternate pairings of numbers and words, such that an end user could
                have the processed number "7” output “Bar”

    Part 2: Implement Fibonacci
        Requirements:
            • Allow the user to enter a number, then display all numbers in the Fibonacci sequence up to the
            entered number, where the starting numbers in the sequence are 0 and 1. f(0) = 0, f(1) = 1.
            • Allow the user to create alternate starting values x and y such that the Fibonacci sequence
            starts with x, y, (x + y), … f(0) = x, f(1) = y
            Fibonacci sequence is a series of numbers where each number is the sum of the two preceding
            ones. The mathematical equation being f(n)=f(n−1) + f(n−2)
            It typically starts with 0, 1, 1, 2, 3, 5, 8, 13, 21, 34, …
    Part 3: Combine FizzBuzz and Fibonacci
        Requirements:
            • Allow the user to enter a number, then display all numbers in the Fibonacci sequence up to the
            entered number, where each number that satisfies the conditions of “FizzBuzz” are replaced
            accordingly. All other requirements still apply.
            • Expected output with default FizzBuzz / Fibonacci values: 0, 1, 1, 2, Fizz, Buzz, 8, 13, 21, 34, …