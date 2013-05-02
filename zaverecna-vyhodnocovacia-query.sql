SELECT bid, questions AS qc, total / users AS average, max, 100 * total / users / max AS percent
FROM (
    SELECT b.bid, SUM(b.points) / IF (b.bid = 16, 2, 3) AS total,
           b.size AS questions, b.size * b.points AS max, (SELECT COUNT(*) FROM users) AS users
    FROM (
        SELECT pid, qorder, qsubord, MAX(serial) AS maxserial
        FROM events
        GROUP BY pid, qorder, qsubord
        ) AS m
        JOIN events AS e
            ON  e.pid     = m.pid
            AND e.qorder  = m.qorder
            AND e.qsubord = m.qsubord
            AND e.serial  = m.maxserial
        JOIN user_questions AS uq
            ON  e.pid    = uq.pid
            AND e.qorder = uq.qorder
        JOIN subquestions AS sq
            ON  sq.qid     = uq.qid
            AND sq.qsubord = e.qsubord
        JOIN questions AS q
            ON  q.qid = sq.qid
        JOIN buckets AS b
            ON  b.bid = q.bid
    WHERE e.value = sq.value
    GROUP BY b.bid, b.size, b.points
    ) AS T
ORDER BY bid;

