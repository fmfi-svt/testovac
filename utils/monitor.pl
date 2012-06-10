#! /usr/bin/perl -w
use strict;

my $cols = 2;

my $query2 = "
select count(u.pid)
from users as u
where printed = 1;
";

my $query = "
select u.pid, count(serial), count(distinct qorder,qsubord), max(time)-begintime, submitted, u.begintime
from users as u
left join events as e on u.pid = e.pid
where printed = 0
group by e.pid
order by u.pid;
";

my $cmd = "mysql -N -u root testovac";

print "Vytlacene: ";
print `$cmd -e '$query2'`;
print "\n";

open IN, "$cmd -e '$query' |";
my $num = 0;
my $odovzdane = 0;
my $expirovane = 0;
while (my $line = <IN>) {
	chomp $line;
	my @parts = split '\t', $line;

	my $time = time - $parts[5];

	print $parts[0];
	if ($parts[4]) {
	    print " odovzdane ";
	    $odovzdane++;
	} elsif ($time > 60 * 60) {
	    print " expirovane";
	    $expirovane++;
	} else {
	    printf " cas: %2d:%02d", $time / 60, $time % 60;
	}

	printf " odp: %3d", $parts[2];
	printf " (ev: %3d last: ", $parts[1];
	if ($parts[3] eq "NULL") {
	    printf " N/A ";
	} else {
	    printf "%2d:%02d", $parts[3] / 60, $parts[3] % 60;
	}
	print ")";

	$num++;

	if ($num % $cols == 0) {
		print "\n";
	} else {
		print " | ";
	}
}
print "\n";
print "\n" if $num % $cols != 0;
printf "Celkovy pocet: %2d\n", $num;
printf " - odovzdane : %2d\n", $odovzdane;
printf " - expirovane: %2d\n", $expirovane;
printf " - vyplna    : %2d\n", $num - $odovzdane - $expirovane;
close IN;
